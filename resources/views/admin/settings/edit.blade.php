@extends('admin.layout')

@section('title', 'Configurações')
@section('heading', 'Configurações da loja')

@section('content')
<div class="panel-card panel-card-body">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <h3 class="panel-section-title mb-4">Geral</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="panel-label">Nome da loja</label>
                <input class="panel-input" type="text" name="app.name" value="{{ old('app.name', $settings['app.name']) }}" required>
            </div>
            <div>
                <label class="panel-label">Email de contato</label>
                <input class="panel-input" type="email" name="app.contact_email" value="{{ old('app.contact_email', $settings['app.contact_email']) }}">
            </div>
        </div>

        <h3 class="panel-section-title mt-8 mb-4">Frete</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="panel-label">Frete padrão (R$)</label>
                <input class="panel-input" type="number" step="0.01" min="0" name="store.shipping_fee" value="{{ old('store.shipping_fee', $settings['store.shipping_fee']) }}" required>
            </div>
        </div>

        <label class="panel-label mt-4">Provider de frete</label>
        <select class="panel-select" name="store.shipping_provider" required>
            <option value="table" {{ old('store.shipping_provider', $settings['store.shipping_provider']) === 'table' ? 'selected' : '' }}>Tabela local</option>
            <option value="melhorenvio" {{ old('store.shipping_provider', $settings['store.shipping_provider']) === 'melhorenvio' ? 'selected' : '' }}>Correios via Melhor Envio</option>
        </select>

        <h3 class="panel-section-title mt-8 mb-4">Origem do envio</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div><label class="panel-label">Nome</label><input class="panel-input" type="text" name="store.origin.name" value="{{ old('store.origin.name', $settings['store.origin.name']) }}"></div>
            <div><label class="panel-label">Rua 1</label><input class="panel-input" type="text" name="store.origin.street1" value="{{ old('store.origin.street1', $settings['store.origin.street1']) }}"></div>
            <div><label class="panel-label">Rua 2</label><input class="panel-input" type="text" name="store.origin.street2" value="{{ old('store.origin.street2', $settings['store.origin.street2']) }}"></div>
            <div><label class="panel-label">Cidade</label><input class="panel-input" type="text" name="store.origin.city" value="{{ old('store.origin.city', $settings['store.origin.city']) }}"></div>
            <div><label class="panel-label">CEP</label><input class="panel-input" type="text" name="store.origin.postcode" value="{{ old('store.origin.postcode', $settings['store.origin.postcode']) }}"></div>
            <div><label class="panel-label">País (2 letras)</label><input class="panel-input" type="text" name="store.origin.country" value="{{ old('store.origin.country', $settings['store.origin.country']) }}"></div>
            <div><label class="panel-label">Telefone</label><input class="panel-input" type="text" name="store.origin.phone" value="{{ old('store.origin.phone', $settings['store.origin.phone']) }}"></div>
            <div><label class="panel-label">Email</label><input class="panel-input" type="email" name="store.origin.email" value="{{ old('store.origin.email', $settings['store.origin.email']) }}"></div>
        </div>

        <h3 class="panel-section-title mt-8 mb-4">Melhor Envio / Correios</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div><label class="panel-label">Token</label><input class="panel-input" type="text" name="store.melhorenvio.token" value="{{ old('store.melhorenvio.token', $settings['store.melhorenvio.token']) }}"></div>
            <div><label class="panel-label">Base URL</label><input class="panel-input" type="url" name="store.melhorenvio.base_url" value="{{ old('store.melhorenvio.base_url', $settings['store.melhorenvio.base_url']) }}"></div>
        </div>

        <button class="panel-btn-primary mt-8" type="submit">Salvar configurações</button>
    </form>
</div>
@endsection
