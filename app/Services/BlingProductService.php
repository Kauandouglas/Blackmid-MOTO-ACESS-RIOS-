<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
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
            'nome' => $search !== '' ? $search : null,
        ]));

        return collect($payload['data'] ?? [])
            ->map(fn (array $item) => $this->normalizeProduct($item))
            ->filter(fn (array $item) => filled($item['bling_id']) && filled($item['name']))
            ->filter(fn (array $item) => $this->matchesSearch($item, $search))
            ->values()
            ->all();
    }

    public function searchAllProducts(string $search = '', int $maxPages = 20, int $limit = 100): array
    {
        $products = collect();
        $page = 1;
        $limit = min(max(1, $limit), 100);

        do {
            $pageProducts = $this->searchProducts($search, $page, $limit);

            $products = $products->concat($pageProducts);

            $hasNextPage = count($pageProducts) === $limit && $page < $maxPages;
            $page++;
        } while ($hasNextPage);

        return $products
            ->unique('bling_id')
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

        if (($product['formato'] ?? null) === 'V') {
            $variationPayload = $this->request('get', "/produtos/variacoes/{$blingId}");
            $variationProduct = $variationPayload['data'] ?? [];

            if (is_array($variationProduct) && ! empty($variationProduct)) {
                $product = array_replace_recursive($product, $variationProduct);
            }
        }

        return $this->normalizeProduct($product);
    }

    public function isConfigured(): bool
    {
        return filled($this->accessToken()) || (
            filled($this->refreshToken())
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

            if ($this->shouldRefreshAccessToken($response)) {
                if (! $this->refreshAccessToken()) {
                    throw new RuntimeException('Sessao do Bling expirada. Abra Conectar Bling e autorize novamente a integracao.');
                }

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

    private function refreshToken(): ?string
    {
        return Cache::get('bling_refresh_token') ?: config('bling.refresh_token');
    }

    private function refreshAccessToken(): bool
    {
        $refreshToken = $this->refreshToken();

        if (! filled($refreshToken) || ! filled(config('bling.client_id')) || ! filled(config('bling.client_secret'))) {
            return false;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->withBasicAuth((string) config('bling.client_id'), (string) config('bling.client_secret'))
            ->timeout((int) config('bling.timeout', 20))
            ->post('https://www.bling.com.br/Api/v3/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => (string) $refreshToken,
            ]);

        if (! $response->successful() || ! filled($response->json('access_token'))) {
            return false;
        }

        $accessToken = (string) $response->json('access_token');
        $refreshToken = (string) ($response->json('refresh_token') ?: $refreshToken);

        $this->env->set([
            'BLING_ACCESS_TOKEN' => $accessToken,
            'BLING_REFRESH_TOKEN' => $refreshToken,
        ]);

        config([
            'bling.access_token' => $accessToken,
            'bling.refresh_token' => $refreshToken,
        ]);

        Cache::put('bling_access_token', $accessToken, now()->addMinutes(50));
        Cache::put('bling_refresh_token', $refreshToken, now()->addDays(29));

        return true;
    }

    private function shouldRefreshAccessToken(Response $response): bool
    {
        if ($response->status() === 401) {
            return true;
        }

        $message = strtolower((string) (
            $response->json('error.description')
            ?? $response->json('error.message')
            ?? $response->body()
        ));

        return str_contains($message, 'access token')
            && (str_contains($message, 'invalid') || str_contains($message, 'expired'));
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

        $imageUrls = $this->imageUrls($item);
        $variants = $this->variants($item);

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
            'image' => $imageUrls[0] ?? null,
            'images' => $imageUrls,
            'sizes' => collect($variants)->pluck('size')->filter()->unique()->values()->all(),
            'colors' => collect($variants)->pluck('color')->filter()->unique()->values()->all(),
            'variants' => $variants,
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

    private function variants(array $item): array
    {
        $rawVariants = data_get($item, 'variacoes', []);

        if (! is_array($rawVariants)) {
            return [];
        }

        return collect($rawVariants)
            ->map(function ($variant) {
                if (! is_array($variant)) {
                    return null;
                }

                $attributes = $this->variationAttributes((string) data_get($variant, 'variacao.nome', ''));
                $size = $attributes['tamanho'] ?? $attributes['tam'] ?? $attributes['size'] ?? '';
                $color = $attributes['cor'] ?? $attributes['color'] ?? '';

                return [
                    'bling_id' => (string) data_get($variant, 'id', ''),
                    'code' => (string) (data_get($variant, 'codigo') ?? ''),
                    'size' => trim((string) $size),
                    'color' => trim((string) $color),
                    'stock' => max(0, (int) round($this->number(
                        data_get($variant, 'estoque.saldoVirtualTotal')
                        ?? data_get($variant, 'estoque.saldoFisicoTotal')
                        ?? data_get($variant, 'estoqueAtual')
                    ))),
                    'price' => $this->number(data_get($variant, 'preco') ?? data_get($variant, 'precoVenda')),
                ];
            })
            ->filter(fn (?array $variant) => $variant && ($variant['size'] !== '' || $variant['color'] !== ''))
            ->unique(fn (array $variant) => mb_strtolower($variant['size'].'|'.$variant['color']))
            ->values()
            ->all();
    }

    private function variationAttributes(string $name): array
    {
        return collect(preg_split('/[;|,]+/', $name) ?: [])
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode(':', $part, 2), 2, '');
                $key = $this->normalizeText($key);
                $value = trim($value);

                return $key !== '' && $value !== '' ? [$key => $value] : [];
            })
            ->all();
    }

    private function imageUrls(array $item): array
    {
        $candidates = Arr::flatten([
            data_get($item, 'midia.imagens.externas.*.link', []),
            data_get($item, 'midia.imagens.externas.*.url', []),
            data_get($item, 'midia.imagens.externas.*', []),
            data_get($item, 'midia.imagens.internas.*.link', []),
            data_get($item, 'midia.imagens.internas.*.url', []),
            data_get($item, 'midia.imagens.internas.*', []),
            data_get($item, 'midia.imagens.*.link', []),
            data_get($item, 'midia.imagens.*.url', []),
            data_get($item, 'imagemURL'),
            data_get($item, 'image'),
        ]);

        return collect($candidates)
            ->filter(fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL))
            ->map(fn (string $url) => trim($url))
            ->unique()
            ->values()
            ->all();
    }
}
