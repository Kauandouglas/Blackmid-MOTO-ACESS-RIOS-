<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    private const KEYS = [
        'payments.mercadopago.enabled',
        'payments.mercadopago.access_token',
        'payments.mercadopago.webhook_secret',
    ];

    public function edit(): View
    {
        $saved = StoreSetting::getMany(self::KEYS);

        // Preenche com valores atuais do .env quando não houver override no banco
        $settings = [
            'payments.mercadopago.enabled'      => $saved['payments.mercadopago.enabled'] ?? '1',
            'payments.mercadopago.access_token' => $saved['payments.mercadopago.access_token'] ?? config('services.mercadopago.access_token'),
            'payments.mercadopago.webhook_secret' => $saved['payments.mercadopago.webhook_secret'] ?? config('services.mercadopago.webhook_secret'),
        ];

        return view('admin.settings.payments', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mercadopago_enabled'       => ['nullable', 'in:0,1'],
            'mercadopago_access_token'  => ['nullable', 'string', 'max:500'],
            'mercadopago_webhook_secret' => ['nullable', 'string', 'max:500'],
        ]);

        StoreSetting::setMany([
            'payments.mercadopago.enabled'        => $data['mercadopago_enabled'] ?? '1',
            'payments.mercadopago.access_token'   => $data['mercadopago_access_token'] ?? null,
            'payments.mercadopago.webhook_secret' => $data['mercadopago_webhook_secret'] ?? null,
        ]);

        return back()->with('success', 'Configurações de pagamento atualizadas com sucesso.');
    }

    /**
     * Verifica se um gateway está habilitado.
     */
    public static function isGatewayEnabled(string $gateway): bool
    {
        $key = "payments.{$gateway}.enabled";
        $saved = StoreSetting::getMany([$key]);

        // Se nunca foi salvo, considera habilitado (default)
        if ($saved[$key] === null) {
            return true;
        }

        return $saved[$key] === '1';
    }

    /**
     * Retorna a lista de gateways ativos.
     */
    public static function enabledGateways(): array
    {
        return self::isGatewayEnabled('mercadopago') ? ['mercadopago'] : [];
    }
}
