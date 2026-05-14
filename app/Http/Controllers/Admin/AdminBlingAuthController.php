<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EnvFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AdminBlingAuthController extends Controller
{
    public function show(Request $request): View
    {
        return view('admin.bling.auth', [
            'callbackUrl' => rtrim($request->getSchemeAndHttpHost(), '/').route('admin.bling.callback', [], false),
            'clientIdConfigured' => filled(config('bling.client_id')),
            'clientSecretConfigured' => filled(config('bling.client_secret')),
            'accessTokenConfigured' => filled(config('bling.access_token')) || filled(Cache::get('bling_access_token')),
            'refreshTokenConfigured' => filled(config('bling.refresh_token')),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        if (! filled(config('bling.client_id')) || ! filled(config('bling.client_secret'))) {
            return redirect()
                ->route('admin.bling.auth')
                ->with('error', 'Preencha BLING_CLIENT_ID e BLING_CLIENT_SECRET no .env antes de conectar.');
        }

        $state = Str::random(40);
        $request->session()->put('bling_oauth_state', $state);

        $url = 'https://www.bling.com.br/Api/v3/oauth/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => (string) config('bling.client_id'),
            'state' => $state,
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request, EnvFileService $env): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('admin.bling.auth')
                ->with('error', 'Bling recusou a autorizacao: '.$request->query('error'));
        }

        $state = (string) $request->query('state', '');
        $expectedState = (string) $request->session()->pull('bling_oauth_state', '');

        if ($expectedState === '' || ! hash_equals($expectedState, $state)) {
            return redirect()
                ->route('admin.bling.auth')
                ->with('error', 'Retorno do Bling invalido. Tente conectar novamente.');
        }

        $code = (string) $request->query('code', '');

        if ($code === '') {
            return redirect()
                ->route('admin.bling.auth')
                ->with('error', 'Bling nao retornou o codigo de autorizacao.');
        }

        try {
            $tokens = $this->exchangeCode($code);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.bling.auth')
                ->with('error', $exception->getMessage());
        }

        $env->set([
            'BLING_ACCESS_TOKEN' => $tokens['access_token'],
            'BLING_REFRESH_TOKEN' => $tokens['refresh_token'],
        ]);

        config([
            'bling.access_token' => $tokens['access_token'],
            'bling.refresh_token' => $tokens['refresh_token'],
        ]);

        Cache::put('bling_access_token', $tokens['access_token'], now()->addMinutes(50));

        return redirect()
            ->route('admin.bling.products.index')
            ->with('success', 'Bling conectado com sucesso. Agora voce ja pode buscar e importar produtos.');
    }

    private function exchangeCode(string $code): array
    {
        $response = Http::asForm()
            ->acceptJson()
            ->withBasicAuth((string) config('bling.client_id'), (string) config('bling.client_secret'))
            ->timeout((int) config('bling.timeout', 20))
            ->post('https://www.bling.com.br/Api/v3/oauth/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

        if (! $response->successful()) {
            $message = $response->json('error.description')
                ?? $response->json('error.message')
                ?? $response->body();

            throw new RuntimeException('Falha ao conectar no Bling: '.mb_substr((string) $message, 0, 500));
        }

        $accessToken = (string) $response->json('access_token', '');
        $refreshToken = (string) $response->json('refresh_token', '');

        if ($accessToken === '' || $refreshToken === '') {
            throw new RuntimeException('Bling nao retornou access_token e refresh_token.');
        }

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }
}
