@extends('admin.layout')
@section('title', 'Pagamentos')
@section('heading', 'Pagamentos')

@section('content')
<form action="{{ route('admin.payments.update') }}" method="POST" class="space-y-6 max-w-3xl">
    @csrf
    @method('PUT')

    {{-- ─── MERCADO PAGO ─── --}}
    {{-- ─── MERCADO PAGO ─── --}}
    <div class="panel-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-line px-5 py-4 lg:px-6">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-yellow-100 text-yellow-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
                </span>
                <div>
                    <h2 class="panel-section-title">Mercado Pago</h2>
                    <p class="text-xs text-muted mt-0.5">Cartão, PIX e Boleto</p>
                </div>
            </div>

            {{-- Toggle --}}
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="mercadopago_enabled" value="0">
                <input type="checkbox" name="mercadopago_enabled" value="1" class="sr-only peer"
                    {{ ($settings['payments.mercadopago.enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-brand/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                <span class="ml-2 text-sm font-bold" id="mercadopago-status">
                    {{ ($settings['payments.mercadopago.enabled'] ?? '1') === '1' ? 'Ativo' : 'Desativado' }}
                </span>
            </label>
        </div>

        <div class="panel-card-body space-y-4" id="mercadopago-fields">
            <div>
                <label class="panel-label" for="mercadopago_access_token">Access Token</label>
                <input type="password" name="mercadopago_access_token" id="mercadopago_access_token"
                    class="panel-input font-mono text-xs"
                    value="{{ old('mercadopago_access_token', $settings['payments.mercadopago.access_token'] ?? '') }}"
                    placeholder="APP_USR_...">
                <p class="text-[11px] text-muted mt-1">Encontrado em Configurações → Credenciais no painel do Mercado Pago.</p>
                @error('mercadopago_access_token') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="panel-label" for="mercadopago_webhook_secret">Webhook Secret</label>
                <input type="password" name="mercadopago_webhook_secret" id="mercadopago_webhook_secret"
                    class="panel-input font-mono text-xs"
                    value="{{ old('mercadopago_webhook_secret', $settings['payments.mercadopago.webhook_secret'] ?? '') }}"
                    placeholder="Chave do webhook...">
                <p class="text-[11px] text-muted mt-1">Configure em Webhooks → Seu App → Adicionar Webhook.</p>
                @error('mercadopago_webhook_secret') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-900 font-semibold mb-2">Métodos de Pagamento Disponíveis:</p>
                <ul class="text-xs text-blue-800 space-y-1 list-disc list-inside">
                    <li>💳 Cartão de Crédito</li>
                    <li>💳 Cartão de Débito</li>
                    <li>📱 PIX (Instantâneo)</li>
                    <li>📋 Boleto Bancário</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Salvar --}}
    <div class="flex justify-end">
        <button type="submit" class="panel-btn-primary gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Salvar configurações
        </button>
    </div>
</form>

<script>
    // Toggle visual para Mercado Pago
    document.querySelector('input[name="mercadopago_enabled"][type="checkbox"]').addEventListener('change', function () {
        const fields = document.getElementById('mercadopago-fields');
        const status = document.getElementById('mercadopago-status');
        if (this.checked) {
            fields.style.opacity = '1';
            fields.style.pointerEvents = 'auto';
            status.textContent = 'Ativo';
        } else {
            fields.style.opacity = '0.4';
            fields.style.pointerEvents = 'none';
            status.textContent = 'Desativado';
        }
    });

    // Estado inicial
    document.addEventListener('DOMContentLoaded', function () {
        const cb = document.querySelector('input[name="mercadopago_enabled"][type="checkbox"]');
        if (cb && !cb.checked) {
            const fields = document.getElementById('mercadopago-fields');
            fields.style.opacity = '0.4';
            fields.style.pointerEvents = 'none';
        }
    });
</script>
@endsection
