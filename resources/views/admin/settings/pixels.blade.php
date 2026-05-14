@extends('admin.layout')

@section('title', 'Pixels & Marketing')
@section('heading', 'Pixels & Marketing')

@section('content')
<div class="panel-card panel-card-body">
    <form method="POST" action="{{ route('admin.pixel-marketing.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-6 rounded-3xl border border-blue-100 bg-blue-50 px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Facebook Pixel</p>
            <p class="mt-2 text-sm text-blue-900/80">Configure o Pixel do Facebook e a moeda usada apenas nos eventos enviados para o Meta Pixel.</p>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                <label class="panel-label">Script HTML completo do Facebook Pixel</label>
                <textarea class="panel-input min-h-[220px] font-mono text-xs leading-6" name="store_pixel_facebook" placeholder="Cole aqui o script completo do Pixel...">{{ old('store_pixel_facebook', $settings['store.pixel.facebook']) }}</textarea>
                <p class="mt-1.5 text-xs text-muted">Pode colar o bloco completo com &lt;script&gt; e &lt;noscript&gt;. Se preferir, um ID numérico antigo também continua funcionando.</p>
            </div>

            <div>
                <label class="panel-label">Moeda dos eventos do Pixel</label>
              <input class="panel-input" type="text" name="store_pixel_facebook_currency"
                  value="{{ old('store_pixel_facebook_currency', $settings['store.pixel.facebook_currency']) }}"
                       placeholder="Ex: BRL, USD, EUR"
                       maxlength="3"
                       required>
                <p class="mt-1.5 text-xs text-muted">Use o código ISO de 3 letras. Ex.: BRL, USD, EUR.</p>
            </div>
        </div>

        <div class="mt-6 rounded-3xl border border-line bg-cloud px-5 py-5">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Eventos automáticos</p>
            <ul class="mt-3 space-y-2 text-sm text-muted">
                <li><strong class="text-ink">PageView</strong> — em todas as páginas</li>
                <li><strong class="text-ink">ViewContent</strong> — página do produto</li>
                <li><strong class="text-ink">AddToCart</strong> — ao adicionar ao carrinho</li>
                <li><strong class="text-ink">InitiateCheckout</strong> — ao entrar no checkout</li>
                <li><strong class="text-ink">AddPaymentInfo</strong> — ao confirmar o metodo de pagamento</li>
                <li><strong class="text-ink">Purchase</strong> — ao concluir a compra</li>
                <li><strong class="text-ink">Search</strong> — ao buscar produtos</li>
            </ul>
        </div>

        <button class="panel-btn-primary mt-8" type="submit">Salvar configurações</button>
    </form>
</div>
@endsection
