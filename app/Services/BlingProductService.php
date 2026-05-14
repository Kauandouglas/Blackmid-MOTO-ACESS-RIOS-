<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BlingProductService
{
    public function __construct(private readonly EnvFileService $env)
    {
    }

    public function searchProducts(string $search = '', int $page = 1, int $limit = 100): array
    {
        $payload = $this->request('get', '/produtos', array_filter([
            'pagina' => max(1, $page),
            'limite' => min(max(1, $limit), 100),
            'criterio' => $search !== '' ? $search : null,
        ]));

        return collect($payload['data'] ?? [])
            ->map(fn (array $item) => $this->normalizeProduct($item))
            ->filter(fn (array $item) => filled($item['bling_id']) && filled($item['name']))
            ->filter(fn (array $item) => $this->matchesSearch($item, $search))
            ->values()
            ->all();
    }

    public function getProduct(string $blingId): array
    {
        $payload = $this->request('get', "/produtos/{$blingId}");
        $product = $payload['data'] ?? [];

        if (! is_array($product) || empty($product)) {
            throw new RuntimeException("Produto {$blingId} nao encontrado no Bling.");
        }

        return $this->normalizeProduct($product);
    }

    public function isConfigured(): bool
    {
        return filled($this->accessToken()) || (
            filled(config('bling.refresh_token'))
            && filled(config('bling.client_id'))
            && filled(config('bling.client_secret'))
        );
    }

    private function request(string $method, string $path, array $query = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Configure BLING_ACCESS_TOKEN no .env antes de buscar produtos.');
        }

        try {
            $response = $this->http()->{$method}($this->url($path), $query);

            if ($response->status() === 401 && $this->refreshAccessToken()) {
                $response = $this->http()->{$method}($this->url($path), $query);
            }

            $response->throw();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('error.description')
                ?? $exception->response?->json('error.message')
                ?? $exception->response?->body()
                ?? $exception->getMessage();

            throw new RuntimeException('Falha na API do Bling: '.mb_substr((string) $message, 0, 500));
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Bling retornou uma resposta invalida.');
        }

        return $payload;
    }

    private function http()
    {
        return Http::acceptJson()
            ->withToken($this->accessToken())
            ->timeout((int) config('bling.timeout', 20));
    }

    private function accessToken(): ?string
    {
        return Cache::get('bling_access_token') ?: config('bling.access_token');
    }

    private function refreshAccessToken(): bool
    {
        if (! filled(config('bling.refresh_token')) || ! filled(config('bling.client_id')) || ! filled(config('bling.client_secret'))) {
            return false;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->withBasicAuth((string) config('bling.client_id'), (string) config('bling.client_secret'))
            ->timeout((int) config('bling.timeout', 20))
            ->post('https://www.bling.com.br/Api/v3/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => (string) config('bling.refresh_token'),
            ]);

        if (! $response->successful() || ! filled($response->json('access_token'))) {
            return false;
        }

        $accessToken = (string) $response->json('access_token');
        $refreshToken = (string) ($response->json('refresh_token') ?: config('bling.refresh_token'));

        $this->env->set([
            'BLING_ACCESS_TOKEN' => $accessToken,
            'BLING_REFRESH_TOKEN' => $refreshToken,
        ]);

        config([
            'bling.access_token' => $accessToken,
            'bling.refresh_token' => $refreshToken,
        ]);

        Cache::put('bling_access_token', $accessToken, now()->addMinutes(50));

        return true;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('bling.base_url'), '/').'/'.ltrim($path, '/');
    }

    private function normalizeProduct(array $item): array
    {
        $netWeight = $this->number(data_get($item, 'dimensoes.pesoLiquido')
            ?? data_get($item, 'pesoLiquido')
            ?? data_get($item, 'peso'));
        $grossWeight = $this->number(data_get($item, 'dimensoes.pesoBruto')
            ?? data_get($item, 'pesoBruto')
            ?? $netWeight);

        return [
            'bling_id' => (string) data_get($item, 'id', ''),
            'code' => (string) (data_get($item, 'codigo') ?? ''),
            'name' => trim((string) (data_get($item, 'nome') ?? data_get($item, 'descricao') ?? '')),
            'description' => (string) (
                data_get($item, 'descricaoCurta')
                ?? data_get($item, 'descricaoComplementar')
                ?? ''
            ),
            'observations' => (string) (
                data_get($item, 'observacoes')
                ?? data_get($item, 'observacao')
                ?? data_get($item, 'descricaoComplementar')
                ?? ''
            ),
            'price' => $this->number(data_get($item, 'preco') ?? data_get($item, 'precoVenda')),
            'stock' => max(0, (int) round($this->number(
                data_get($item, 'estoque.saldoVirtualTotal')
                ?? data_get($item, 'estoque.saldoFisicoTotal')
                ?? data_get($item, 'estoqueAtual')
            ))),
            'weight_grams' => $this->weightToGrams($netWeight),
            'gross_weight_grams' => $this->weightToGrams($grossWeight),
            'width_cm' => $this->dimensionToCm(data_get($item, 'dimensoes.largura') ?? data_get($item, 'largura')),
            'height_cm' => $this->dimensionToCm(data_get($item, 'dimensoes.altura') ?? data_get($item, 'altura')),
            'depth_cm' => $this->dimensionToCm(data_get($item, 'dimensoes.profundidade') ?? data_get($item, 'profundidade')),
            'image' => $this->imageUrl($item),
            'active' => ! in_array(strtolower((string) data_get($item, 'situacao')), ['i', 'inativo'], true),
        ];
    }

    private function number(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function matchesSearch(array $item, string $search): bool
    {
        $terms = collect(preg_split('/\s+/', $this->normalizeText($search)) ?: [])
            ->filter()
            ->values();

        if ($terms->isEmpty()) {
            return true;
        }

        $haystack = $this->normalizeText(implode(' ', [
            $item['name'] ?? '',
            $item['code'] ?? '',
            strip_tags((string) ($item['description'] ?? '')),
            strip_tags((string) ($item['observations'] ?? '')),
        ]));

        return $terms->every(fn (string $term) => str_contains($haystack, $term));
    }

    private function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return $converted !== false ? $converted : $value;
    }

    private function weightToGrams(float $weight): int
    {
        if ($weight <= 0) {
            return 0;
        }

        return max(1, (int) round($weight < 100 ? $weight * 1000 : $weight));
    }

    private function dimensionToCm(mixed $value): ?float
    {
        $dimension = $this->number($value);

        return $dimension > 0 ? round($dimension, 2) : null;
    }

    private function imageUrl(array $item): ?string
    {
        $url = data_get($item, 'midia.imagens.externas.0.link')
            ?? data_get($item, 'midia.imagens.internas.0.link')
            ?? data_get($item, 'imagemURL')
            ?? data_get($item, 'image');

        return filter_var($url, FILTER_VALIDATE_URL) ? (string) $url : null;
    }
}
