<?php

namespace Tests\Unit;

use App\Services\BlingProductService;
use App\Services\EnvFileService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BlingProductServiceTest extends TestCase
{
    public function test_it_refreshes_token_when_bling_reports_expired_access_token(): void
    {
        config([
            'bling.base_url' => 'https://api.bling.com.br/Api/v3',
            'bling.access_token' => 'old-access-token',
            'bling.refresh_token' => 'old-refresh-token',
            'bling.client_id' => 'client-id',
            'bling.client_secret' => 'client-secret',
            'bling.timeout' => 20,
        ]);

        Cache::flush();
        Cache::put('bling_access_token', 'cached-old-access-token', now()->addMinutes(50));
        Cache::put('bling_refresh_token', 'cached-old-refresh-token', now()->addDays(29));

        Http::fake([
            'https://api.bling.com.br/Api/v3/produtos*' => Http::sequence()
                ->push(['error' => ['description' => 'The access token provided is invalid or expired']], 400)
                ->push(['data' => [
                    [
                        'id' => 123,
                        'nome' => 'Filtro de oleo',
                        'codigo' => 'FO-1',
                        'preco' => 25.5,
                    ],
                ]], 200),
            'https://www.bling.com.br/Api/v3/oauth/token' => Http::response([
                'access_token' => 'new-access-token',
                'refresh_token' => 'new-refresh-token',
            ], 200),
        ]);

        $env = new class extends EnvFileService
        {
            public array $values = [];

            public function set(array $values): void
            {
                $this->values = $values;
            }
        };

        $products = (new BlingProductService($env))->searchProducts('Filtro');

        $this->assertSame('123', $products[0]['bling_id']);
        $this->assertSame('Filtro de oleo', $products[0]['name']);
        $this->assertSame('new-access-token', Cache::get('bling_access_token'));
        $this->assertSame('new-refresh-token', Cache::get('bling_refresh_token'));
        $this->assertSame([
            'BLING_ACCESS_TOKEN' => 'new-access-token',
            'BLING_REFRESH_TOKEN' => 'new-refresh-token',
        ], $env->values);

        Http::assertSentCount(3);
    }

    public function test_it_uses_bling_product_name_filter_when_searching(): void
    {
        config([
            'bling.base_url' => 'https://api.bling.com.br/Api/v3',
            'bling.access_token' => 'access-token',
            'bling.refresh_token' => null,
            'bling.client_id' => null,
            'bling.client_secret' => null,
            'bling.timeout' => 20,
        ]);

        Cache::flush();

        Http::fake([
            'https://api.bling.com.br/Api/v3/produtos*' => Http::response(['data' => [
                [
                    'id' => 456,
                    'nome' => 'Capacete Pro Tork',
                    'codigo' => 'CAP-1',
                    'preco' => 199.9,
                ],
            ]], 200),
        ]);

        $products = (new BlingProductService(new EnvFileService()))->searchProducts('capacete');

        $this->assertSame('456', $products[0]['bling_id']);

        Http::assertSent(function ($request) {
            $query = [];
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->url() !== ''
                && ($query['nome'] ?? null) === 'capacete'
                && ! array_key_exists('criterio', $query);
        });
    }
}
