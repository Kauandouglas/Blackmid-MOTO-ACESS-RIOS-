@extends('layouts.app')

@section('title', 'Carrinho - ' . config('app.name'))
@section('bodyClass', 'page-cart')

@section('content')
@if (count($items) === 0)
<section class="cart-empty">
    <div class="container">
        <div class="empty-state">
            <span class="cart-empty__icon"><i class="fa-solid fa-cart-shopping"></i></span>
            <h2>Sua sacola esta vazia</h2>
            <p>Explore capacetes, acessorios, vestuario e pecas para sua moto.</p>
            <a href="{{ route('store.index') }}" class="btn btn-green">EXPLORAR PRODUTOS</a>
        </div>
    </div>
</section>
@else
<section class="cart-page">
    <div class="container">
        <div class="cart-title">
            <div>
                <span class="eyebrow">Carrinho</span>
                <h1>Sua Sacola</h1>
                <p id="cart-item-count">{{ $cartCount }} {{ $cartCount === 1 ? 'item' : 'itens' }} no carrinho</p>
            </div>
            <a href="{{ route('store.index') }}" class="cart-title__link"><i class="fa-solid fa-arrow-left"></i> Continuar comprando</a>
        </div>

        <div class="cart-grid">
            <div class="cart-list" id="cart-items-list">
                <div id="cart-items-inner">
                    @foreach ($items as $item)
                    <article
                        class="cart-item"
                        data-key="{{ $item['key'] }}"
                        data-price="{{ $item['product']->price }}"
                        data-max-stock="{{ $item['max_quantity'] === null ? '' : (int) $item['max_quantity'] }}"
                    >
                        <a href="{{ route('store.show', $item['product']->slug) }}" class="cart-item__image">
                            <img src="{{ $item['product']->image ?: asset('motoacessorios/placeholder-product.svg') }}" alt="{{ $item['product']->name }}">
                        </a>

                        <div class="cart-item__body">
                            <div class="cart-item__top">
                                <div>
                                    <span class="cart-item__cat">{{ $item['product']->category?->name ?? 'Produto' }}</span>
                                    <h2>{{ $item['product']->name }}</h2>
                                    <p>Entrega e frete calculados na finalizacao</p>
                                </div>
                                <button type="button" onclick="removeItem('{{ $item['key'] }}', this)" class="cart-remove" aria-label="Remover item">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>

                            @if($item['size'] || $item['color'])
                                <div class="cart-options">
                                    @if($item['size']) <span>TAM <strong>{{ $item['size'] }}</strong></span> @endif
                                    @if($item['color']) <span>COR <strong>{{ $item['color'] }}</strong></span> @endif
                                </div>
                            @endif

                            <div class="cart-item__bottom">
                                <div class="qty-stepper">
                                    <button type="button" onclick="changeQty('{{ $item['key'] }}', -1, this)" aria-label="Diminuir quantidade" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}><i class="fa-solid fa-minus"></i></button>
                                    <span class="item-qty" data-qty="{{ $item['quantity'] }}">{{ $item['quantity'] }}</span>
                                    <button type="button" onclick="changeQty('{{ $item['key'] }}', +1, this)" aria-label="Aumentar quantidade" {{ $item['max_quantity'] !== null && $item['quantity'] >= $item['max_quantity'] ? 'disabled' : '' }}><i class="fa-solid fa-plus"></i></button>
                                </div>
                                <div class="cart-price">
                                    <span>Total do item</span>
                                    <p class="item-line-total">R$ {{ number_format($item['line_total'], 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                <div class="cart-list__footer">
                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        <button type="submit"><i class="fa-regular fa-trash-can"></i> Limpar sacola</button>
                    </form>
                    <a href="{{ route('store.index') }}"><i class="fa-solid fa-plus"></i> Adicionar produtos</a>
                </div>
            </div>

            <aside class="cart-summary">
                <div class="cart-summary__head">
                    <span><i class="fa-solid fa-receipt"></i></span>
                    <h2>Resumo do pedido</h2>
                </div>
                <div id="summary-lines" class="summary-lines">
                    @foreach ($items as $item)
                    <div class="summary-line" data-summary-key="{{ $item['key'] }}">
                        <span>
                            {{ $item['product']->name }}
                            @if($item['size']) - {{ $item['size'] }}@endif
                            @if($item['color']) - {{ $item['color'] }}@endif
                            <b class="summary-qty"> x{{ $item['quantity'] }}</b>
                        </span>
                        <strong class="summary-line-total">R$ {{ number_format($item['line_total'], 2, ',', '.') }}</strong>
                    </div>
                    @endforeach
                </div>

                <div class="summary-total">
                    <span>Subtotal</span>
                    <strong id="sidebar-subtotal">R$ {{ number_format($subtotal, 2, ',', '.') }}</strong>
                </div>
                <p class="summary-note">Frete calculado no checkout</p>

                <a href="{{ route('checkout.index') }}" class="btn btn--full btn-green cart-checkout-btn">
                    <i class="fa-solid fa-lock"></i> FINALIZAR COMPRA
                </a>

                <div class="summary-security">
                    <span><i class="fa-solid fa-lock"></i> Checkout seguro</span>
                    <span><i class="fa-brands fa-pix"></i> Pix, cartao e boleto</span>
                </div>
            </aside>
        </div>
    </div>
</section>
@endif

<script>
var CART_UPDATE = '{{ route('cart.update') }}';
var CART_REMOVE = '{{ route('cart.remove') }}';
var CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

function money(value) {
    return 'R$ ' + Number(value || 0).toFixed(2).replace('.', ',');
}

function cartFetch(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify(body),
    }).then(function (r) { return r.json(); });
}

function changeQty(key, delta, btn) {
    var article = btn.closest('article[data-key]');
    var qtyEl = article.querySelector('.item-qty');
    var currentQty = parseInt(qtyEl.dataset.qty || '1');
    var maxStockRaw = article.dataset.maxStock;
    var hasMaxStock = maxStockRaw !== '' && !Number.isNaN(Number(maxStockRaw));
    var maxStock = hasMaxStock ? parseInt(maxStockRaw, 10) : null;

    if (delta > 0 && hasMaxStock && currentQty >= maxStock) return;
    if (delta < 0 && currentQty <= 1) return;

    var qty = Math.max(1, currentQty + delta);
    if (hasMaxStock) qty = Math.min(qty, maxStock);
    if (qty === currentQty) return;

    qtyEl.textContent = qty;
    qtyEl.dataset.qty = qty;
    updateQtyButtons(article, qty);

    clearTimeout(article._debounce);
    article._debounce = setTimeout(function () {
        syncQty(key, qty, article);
    }, 300);
}

function syncQty(key, qty, article) {
    cartFetch(CART_UPDATE, { key: key, quantity: qty }).then(function (json) {
        if (!json.success) return;

        if (json.updated_item) {
            var serverQty = Number(json.updated_item.quantity || qty);
            var qtyEl = article.querySelector('.item-qty');
            qtyEl.textContent = serverQty;
            qtyEl.dataset.qty = serverQty;

            if (json.updated_item.available_stock !== null && json.updated_item.available_stock !== undefined) {
                article.dataset.maxStock = Number(json.updated_item.available_stock);
            }

            updateQtyButtons(article, serverQty);
            article.querySelector('.item-line-total').textContent = json.updated_item.line_total_formatted;
            updateSummaryLine(json.updated_item.key, serverQty, json.updated_item.line_total_formatted);
        } else {
            article.remove();
            document.querySelector('[data-summary-key="' + key + '"]')?.remove();
        }

        refreshSidebar(json);
        if (json.cart_count === 0) setTimeout(function () { location.reload(); }, 200);
    });
}

function removeItem(key, btn) {
    var article = btn.closest('article[data-key]');
    article.style.opacity = '0.4';
    article.style.pointerEvents = 'none';

    cartFetch(CART_REMOVE, { key: key }).then(function (json) {
        if (!json.success) {
            article.style.opacity = '1';
            article.style.pointerEvents = '';
            return;
        }

        article.style.transition = 'opacity .25s, transform .25s';
        article.style.opacity = '0';
        article.style.transform = 'translateX(10px)';
        setTimeout(function () { article.remove(); }, 260);
        document.querySelector('[data-summary-key="' + key + '"]')?.remove();

        refreshSidebar(json);
        if (json.cart_count === 0) setTimeout(function () { location.reload(); }, 350);
    });
}

function updateSummaryLine(key, qty, lineFormatted) {
    var line = document.querySelector('[data-summary-key="' + key + '"]');
    if (!line) return;
    line.querySelector('.summary-qty').textContent = ' x' + qty;
    line.querySelector('.summary-line-total').textContent = lineFormatted;
}

function updateQtyButtons(article, qty) {
    if (!article) return;

    var buttons = article.querySelectorAll('.qty-stepper button');
    if (buttons.length < 2) return;

    var maxStockRaw = article.dataset.maxStock;
    var hasMaxStock = maxStockRaw !== '' && !Number.isNaN(Number(maxStockRaw));
    var maxStock = hasMaxStock ? parseInt(maxStockRaw, 10) : null;

    buttons[0].disabled = qty <= 1;
    buttons[1].disabled = hasMaxStock && qty >= maxStock;
}

function refreshSidebar(json) {
    var badge = document.getElementById('cart-badge');
    if (badge) badge.textContent = json.cart_count;

    var countEl = document.getElementById('cart-item-count');
    if (countEl) countEl.textContent = json.cart_count + ' ' + (json.cart_count === 1 ? 'item' : 'itens') + ' no carrinho';

    var subtotalEl = document.getElementById('sidebar-subtotal');
    if (subtotalEl) subtotalEl.textContent = json.subtotal_formatted;

}

document.querySelectorAll('article[data-key]').forEach(function (article) {
    var qtyEl = article.querySelector('.item-qty');
    updateQtyButtons(article, parseInt(qtyEl?.dataset.qty || '1', 10));
});
</script>
@endsection
