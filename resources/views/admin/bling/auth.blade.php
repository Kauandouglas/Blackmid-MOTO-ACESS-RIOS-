@extends('admin.layout')

@section('title', 'Conectar Bling')
@section('heading', 'Conectar Bling')

@section('content')
<div class="grid gap-4 lg:grid-cols-[1fr_360px]">
    <div class="panel-card panel-card-body">
        <h2 class="panel-section-title">URL de redirecionamento</h2>
        <p class="mt-2 text-sm text-slate-600">Cadastre exatamente esta URL no aplicativo do Bling.</p>

        <div class="mt-4 flex flex-col gap-2 rounded-2xl border border-line bg-cloud p-3 sm:flex-row sm:items-center">
            <input class="panel-input font-mono text-xs" value="{{ $callbackUrl }}" readonly data-callback-url>
            <button class="panel-btn-secondary shrink-0" type="button" data-copy-callback>Copiar</button>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-line bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-400">Client ID</p>
                <p class="mt-2 font-bold {{ $clientIdConfigured ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $clientIdConfigured ? 'Configurado' : 'Pendente no .env' }}
                </p>
            </div>
            <div class="rounded-2xl border border-line bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-400">Client Secret</p>
                <p class="mt-2 font-bold {{ $clientSecretConfigured ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $clientSecretConfigured ? 'Configurado' : 'Pendente no .env' }}
                </p>
            </div>
            <div class="rounded-2xl border border-line bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-400">Access Token</p>
                <p class="mt-2 font-bold {{ $accessTokenConfigured ? 'text-emerald-700' : 'text-slate-600' }}">
                    {{ $accessTokenConfigured ? 'Gerado' : 'Ainda nao gerado' }}
                </p>
            </div>
            <div class="rounded-2xl border border-line bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-[0.08em] text-slate-400">Refresh Token</p>
                <p class="mt-2 font-bold {{ $refreshTokenConfigured ? 'text-emerald-700' : 'text-slate-600' }}">
                    {{ $refreshTokenConfigured ? 'Gerado' : 'Ainda nao gerado' }}
                </p>
            </div>
        </div>

        <form class="mt-6" method="POST" action="{{ route('admin.bling.connect') }}">
            @csrf
            <button class="panel-btn-primary" type="submit" {{ ! $clientIdConfigured || ! $clientSecretConfigured ? 'disabled' : '' }}>
                Conectar ao Bling
            </button>
        </form>

        @if(! $clientIdConfigured || ! $clientSecretConfigured)
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                Primeiro preencha BLING_CLIENT_ID e BLING_CLIENT_SECRET no .env. Depois rode php artisan config:clear e volte aqui.
            </div>
        @endif
    </div>

    <aside class="panel-card panel-card-body self-start">
        <h2 class="panel-section-title">Como ativar</h2>
        <div class="mt-4 space-y-3 text-sm text-slate-600">
            <p><strong>1.</strong> Suba o tunel e use a URL publica gerada.</p>
            <p><strong>2.</strong> No Bling, crie o app usando a URL de redirecionamento desta tela.</p>
            <p><strong>3.</strong> Copie Client ID e Client Secret para o .env.</p>
            <p><strong>4.</strong> Clique em Conectar ao Bling e autorize.</p>
        </div>
    </aside>
</div>

@push('scripts')
<script>
document.querySelector('[data-copy-callback]')?.addEventListener('click', async () => {
    const input = document.querySelector('[data-callback-url]');
    if (!input) return;

    await navigator.clipboard.writeText(input.value);
});
</script>
@endpush
@endsection
