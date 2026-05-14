@extends('layouts.app')

@section('title', 'Finalizar Compra - ' . config('app.name'))
@section('bodyClass', 'page-checkout')

@section('content')
@php
    $itemsCount = (int) collect($items)->sum('quantity');
    $contentIds = collect($items)->pluck('product.id')->filter()->map(fn ($id) => (string) $id)->values()->all();
@endphp

<section class="checkout-page">
    <div class="container">
        <nav class="checkout-breadcrumb">
            <a href="{{ route('store.index') }}">Loja</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="{{ route('cart.index') }}">Sacola</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Finalizar compra</span>
        </nav>

        <div class="checkout-title">
            <div>
                <span class="page-kicker">Passo final</span>
                <h1>Finalizar compra</h1>
                <p>Preencha seus dados, escolha o frete e confirme o pagamento com seguranca.</p>
            </div>
            <a href="{{ route('cart.index') }}"><i class="fa-solid fa-arrow-left"></i> Voltar ao carrinho</a>
        </div>

        @if ($errors->any())
            <div class="checkout-errors">
                <strong>Confira os dados abaixo:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $hasInitialShippingQuote = old('shipping_postcode') && old('shipping_method');
            $selectedInitialShipping = old('shipping_method', 'pac');
            $displayTotal = $hasInitialShippingQuote ? $total : max(0, $subtotal - ($discount ?? 0));
        @endphp

        <div class="checkout-layout">
            <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form" class="checkout-panel checkout-form">
                @csrf

                <div class="checkout-section-head">
                    <span><i class="fa-regular fa-envelope"></i></span>
                    <div>
                        <h2>Contato</h2>
                        <p>Usaremos essas informacoes para falar sobre o pedido.</p>
                    </div>
                </div>

                <label class="checkout-field" for="customer_email">
                    <span>E-mail *</span>
                    <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}" required placeholder="cliente.exemplo@email.com">
                </label>

                <label class="checkout-checkline" for="newsletter_opt_in">
                    <input id="newsletter_opt_in" type="checkbox" name="newsletter_opt_in" value="1" {{ old('newsletter_opt_in', true) ? 'checked' : '' }}>
                    <span>Enviar novidades e ofertas para mim por e-mail</span>
                </label>

                <div class="checkout-section-head checkout-section-head--spaced">
                    <span><i class="fa-solid fa-location-dot"></i></span>
                    <div>
                        <h2>Entrega</h2>
                        <p>Informe o CEP para preencher o endereco e calcular o frete.</p>
                    </div>
                </div>

                <label class="checkout-field" for="shipping_country">
                    <span>Pais / Regiao *</span>
                    <select id="shipping_country" name="shipping_country" required>
                        <option value="BR" {{ old('shipping_country', 'BR') === 'BR' ? 'selected' : '' }}>Brasil</option>
                    </select>
                </label>

                <div class="checkout-field-grid checkout-field-grid--two">
                    <label class="checkout-field" for="customer_first_name">
                        <span>Primeiro nome *</span>
                        <input id="customer_first_name" type="text" name="customer_first_name" value="{{ old('customer_first_name') }}" required placeholder="Ex: Nome">
                    </label>
                    <label class="checkout-field" for="customer_last_name">
                        <span>Sobrenome completo *</span>
                        <input id="customer_last_name" type="text" name="customer_last_name" value="{{ old('customer_last_name') }}" required placeholder="Ex: Sobrenome">
                    </label>
                </div>

                <div class="checkout-field checkout-field--cep">
                    <label for="shipping_postcode">
                        <span>CEP *</span>
                        <input id="shipping_postcode" type="text" name="shipping_postcode" value="{{ old('shipping_postcode') }}" required placeholder="12345-678" inputmode="numeric" autocomplete="postal-code">
                    </label>
                    <button type="button" id="cep-search-btn" aria-label="Buscar CEP"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>

                <div class="checkout-field-grid checkout-field-grid--two">
                    <label class="checkout-field" for="shipping_address_line1">
                        <span>Endereco *</span>
                        <input id="shipping_address_line1" type="text" name="shipping_address_line1" value="{{ old('shipping_address_line1') }}" required placeholder="Rua, avenida ou quadra">
                    </label>
                    <label class="checkout-field" for="shipping_number">
                        <span>Numero</span>
                        <input id="shipping_number" type="text" name="shipping_number" value="{{ old('shipping_number') }}" placeholder="123">
                    </label>
                </div>

                <div class="checkout-field-grid checkout-field-grid--two">
                    <label class="checkout-field" for="shipping_address_line2">
                        <span>Complemento</span>
                        <input id="shipping_address_line2" type="text" name="shipping_address_line2" value="{{ old('shipping_address_line2') }}" placeholder="Apto, bloco, sem complemento">
                    </label>
                    <label class="checkout-field" for="shipping_neighborhood">
                        <span>Bairro</span>
                        <input id="shipping_neighborhood" type="text" name="shipping_neighborhood" value="{{ old('shipping_neighborhood') }}" placeholder="Setor, bairro">
                    </label>
                </div>

                <div class="checkout-field-grid checkout-field-grid--two">
                    <label class="checkout-field" for="shipping_city">
                        <span>Cidade *</span>
                        <input id="shipping_city" type="text" name="shipping_city" value="{{ old('shipping_city') }}" required placeholder="Cidade Exemplo">
                    </label>
                    <label class="checkout-field" for="shipping_state">
                        <span>Estado</span>
                        <input id="shipping_state" type="text" name="shipping_state" value="{{ old('shipping_state') }}" placeholder="Estado Exemplo">
                    </label>
                </div>

                <label class="checkout-field" for="customer_phone">
                    <span>Telefone *</span>
                    <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required placeholder="(00) 90000-0000">
                </label>

                <label class="checkout-field" for="customer_document">
                    <span>CPF ou CNPJ *</span>
                    <input id="customer_document" type="text" name="customer_document" value="{{ old('customer_document') }}" required placeholder="000.000.000-00" inputmode="numeric">
                </label>

                <label class="checkout-checkline" for="save_info">
                    <input id="save_info" type="checkbox" name="save_info" value="1" {{ old('save_info') ? 'checked' : '' }}>
                    <span>Salvar minhas informacoes para a proxima vez</span>
                </label>

                <div id="shipping-method-section" {{ $hasInitialShippingQuote ? '' : 'hidden' }}>
                    <div class="checkout-section-head checkout-section-head--spaced">
                        <span><i class="fa-solid fa-truck-fast"></i></span>
                        <div>
                            <h2>Metodo de envio</h2>
                            <p>Escolha uma opcao disponivel para seu endereco.</p>
                        </div>
                    </div>

                    <div class="checkout-options checkout-options--shipping" id="shipping-methods">
                        @foreach($shippingRates as $service => $rate)
                            <label data-shipping-option="{{ $service }}" class="checkout-option {{ $hasInitialShippingQuote && $selectedInitialShipping === $service ? 'is-selected' : '' }}">
                                <input
                                    type="radio"
                                    name="shipping_method"
                                    value="{{ $service }}"
                                    {{ $hasInitialShippingQuote && $selectedInitialShipping === $service ? 'checked' : '' }}
                                    {{ $hasInitialShippingQuote ? '' : 'disabled' }}
                                    onchange="selectShipping('{{ $service }}', {{ $rate['price'] }}, {{ $rate['is_free'] ? 'true' : 'false' }})"
                                    required
                                >
                                <span class="checkout-option__icon"><i class="fa-solid fa-box"></i></span>
                                <span class="checkout-option__body">
                                    <strong data-rate-name="{{ $service }}">{{ $rate['name'] }}</strong>
                                    <small data-rate-eta="{{ $service }}">{{ $rate['eta'] }}</small>
                                </span>
                                <b data-rate-price="{{ $service }}">{{ $rate['is_free'] ? 'Gratis' : 'R$ ' . number_format($rate['price'], 2, ',', '.') }}</b>
                            </label>
                        @endforeach
                    </div>

                    <p class="checkout-note" id="shipping-source-note">
                        @if($hasInitialShippingQuote && ($shippingSource ?? 'fallback') === 'api')
                            Frete calculado pelos Correios usando o CEP informado.
                        @elseif($hasInitialShippingQuote && ($hasShippingApi ?? false))
                            Frete exibido como estimativa. Informe o CEP para buscar a cotacao real dos Correios.
                        @elseif($hasInitialShippingQuote)
                            Frete calculado pela tabela de envio da loja.
                        @else
                            Informe o CEP para buscar a cotacao real.
                        @endif
                    </p>
                    @error('shipping_method')<p class="checkout-error-text">{{ $message }}</p>@enderror
                </div>

                <div class="checkout-section-head checkout-section-head--spaced">
                    <span><i class="fa-solid fa-credit-card"></i></span>
                    <div>
                        <h2>Pagamento</h2>
                        <p>Voce sera direcionado para concluir com seguranca.</p>
                    </div>
                </div>

                <div class="checkout-options">
                    @if(in_array('mercadopago', $enabledGateways))
                        <label class="checkout-option checkout-option--payment {{ old('payment_method', 'mercadopago') === 'mercadopago' ? 'is-selected' : '' }}">
                            <input type="radio" name="payment_method" value="mercadopago" {{ old('payment_method', 'mercadopago') === 'mercadopago' ? 'checked' : '' }} required>
                            <span class="checkout-option__icon"><i class="fa-solid fa-wallet"></i></span>
                            <span class="checkout-option__body">
                                <strong>Mercado Pago</strong>
                                <small>Cartao, Pix e boleto</small>
                            </span>
                            <b>Seguro</b>
                        </label>
                    @endif

                    @if(empty($enabledGateways))
                        <p class="checkout-error-text">Nenhum metodo de pagamento disponivel no momento.</p>
                    @endif
                </div>
                @error('payment_method')<p class="checkout-error-text">{{ $message }}</p>@enderror

                <button type="submit" id="checkout-submit-btn" class="checkout-submit" {{ $hasInitialShippingQuote ? '' : 'disabled' }}>
                    <i class="fa-solid {{ $hasInitialShippingQuote ? 'fa-lock' : 'fa-truck-fast' }}"></i> {{ $hasInitialShippingQuote ? 'Confirmar pedido' : 'Calcule o frete' }}
                </button>

                <p class="checkout-terms">
                    Ao confirmar, voce concorda com nossa
                    <a href="{{ route('store.privacidade') }}">Politica de Privacidade</a>
                    e
                    <a href="{{ route('store.trocas') }}">Politica de Trocas</a>.
                </p>

                <div class="checkout-trust">
                    <span><i class="fa-solid fa-shield-halved"></i> Compra segura</span>
                    <span><i class="fa-solid fa-lock"></i> Dados protegidos</span>
                    <span><i class="fa-solid fa-headset"></i> Atendimento especializado</span>
                </div>
            </form>

            <aside class="checkout-summary">
                <div class="checkout-panel checkout-summary__panel">
                    <div class="checkout-summary__head">
                        <span><i class="fa-solid fa-receipt"></i></span>
                        <div>
                            <h2>Resumo do pedido</h2>
                            <p>{{ $itemsCount }} {{ $itemsCount === 1 ? 'item' : 'itens' }}</p>
                        </div>
                    </div>

                    <div class="checkout-summary__items">
                        @foreach ($items as $item)
                            <article class="checkout-summary-item">
                                <img src="{{ $item['product']->image ?: asset('motoacessorios/placeholder-product.svg') }}" alt="{{ $item['product']->name }}">
                                <div>
                                    <strong>{{ $item['product']->name }}</strong>
                                    <span>
                                        Qtd: {{ $item['quantity'] }}
                                        @if($item['size']) - Tam: {{ $item['size'] }}@endif
                                        @if($item['color']) - Cor: {{ $item['color'] }}@endif
                                    </span>
                                </div>
                                <b>R$ {{ number_format($item['line_total'], 2, ',', '.') }}</b>
                            </article>
                        @endforeach
                    </div>

                    <div class="checkout-coupon-card">
                        <div class="checkout-coupon-card__head">
                            <span><i class="fa-solid fa-ticket"></i></span>
                            <strong>Cupom</strong>
                        </div>
                        <div class="checkout-coupon">
                            <input id="coupon_code" form="checkout-form" type="text" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="Codigo">
                            <button type="button" id="coupon-apply-btn">Aplicar</button>
                        </div>
                        <p class="checkout-note" id="coupon-note"></p>
                    </div>

                    <div class="checkout-totals">
                        <div><span>Subtotal</span><strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong></div>
                        <div><span>Frete</span><strong id="sidebar-shipping">{{ $hasInitialShippingQuote ? ($shipping > 0 ? 'R$ ' . number_format($shipping, 2, ',', '.') : 'Gratis') : 'A calcular' }}</strong></div>
                        <div id="discount-row" style="{{ ($discount ?? 0) > 0 ? '' : 'display:none' }}"><span>Desconto</span><strong id="sidebar-discount">-R$ {{ number_format($discount ?? 0, 2, ',', '.') }}</strong></div>
                        <div class="checkout-totals__grand"><span>Total</span><strong id="sidebar-total">R$ {{ number_format($displayTotal, 2, ',', '.') }}</strong></div>
                    </div>

                    <p class="checkout-shipping-msg" id="shipping-msg">
                        Informe o CEP para calcular o frete dos Correios.
                    </p>

                    <a href="{{ route('cart.index') }}" class="checkout-edit-cart">
                        <i class="fa-solid fa-pen-to-square"></i> Editar sacola
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('pixel_events')
@if(config('store.pixel.facebook'))
<script>
if (typeof fbq === 'function') {
    fbq('track', 'InitiateCheckout', {
        value: {{ (float) $total }},
        currency: '{{ strtoupper((string) config('store.pixel.facebook_currency', 'BRL')) }}',
        num_items: {{ $itemsCount }},
        content_ids: @json($contentIds)
    });
}
</script>
@endif
<script>
var SUBTOTAL = {{ (float) $subtotal }};
var DISCOUNT = {{ (float) ($discount ?? 0) }};
var RATES = @json($shippingRates);
var HAS_SHIPPING_QUOTE = @json((bool) $hasInitialShippingQuote);
var QUOTE_URL = '{{ route('checkout.quote') }}';
var CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
var quoteTimer = null;
var PIXEL_ENABLED = @json((bool) config('store.pixel.facebook'));
var CHECKOUT_PIXEL_DATA = {
    value: {{ (float) $total }},
    currency: @json(strtoupper((string) config('store.pixel.facebook_currency', 'BRL'))),
    num_items: {{ $itemsCount }},
    content_ids: @json($contentIds),
};

function money(value) {
    return 'R$ ' + Number(value || 0).toFixed(2).replace('.', ',');
}

function trackAddPaymentInfo(paymentMethod) {
    if (!PIXEL_ENABLED || typeof fbq !== 'function') return;

    fbq('track', 'AddPaymentInfo', {
        value: CHECKOUT_PIXEL_DATA.value,
        currency: CHECKOUT_PIXEL_DATA.currency,
        content_ids: CHECKOUT_PIXEL_DATA.content_ids,
        content_type: 'product',
        num_items: CHECKOUT_PIXEL_DATA.num_items,
        payment_method: paymentMethod || '',
    });
}

function selectShipping(service, price, isFree) {
    var shippingEl = document.getElementById('sidebar-shipping');
    var totalEl = document.getElementById('sidebar-total');
    var msgEl = document.getElementById('shipping-msg');
    var fee = isFree ? 0 : Number(price || 0);

    if (shippingEl) shippingEl.textContent = isFree ? 'Gratis' : money(fee);
    if (totalEl) totalEl.textContent = money(Math.max(0, SUBTOTAL - DISCOUNT) + fee);

    if (msgEl) {
        msgEl.textContent = service === 'sedex'
            ? 'Frete SEDEX calculado para entrega mais rapida.'
            : 'Frete PAC calculado pelos Correios.';
    }

    document.querySelectorAll('input[name="shipping_method"]').forEach(function (radio) {
        var label = radio.closest('label');
        if (!label) return;
        label.classList.toggle('is-selected', radio.value === service);
    });
}

function selectedShippingService() {
    return document.querySelector('input[name="shipping_method"]:checked')?.value || 'pac';
}

function setShippingReady(isReady) {
    HAS_SHIPPING_QUOTE = !!isReady;

    var section = document.getElementById('shipping-method-section');
    if (section) section.hidden = !HAS_SHIPPING_QUOTE;

    document.querySelectorAll('input[name="shipping_method"]').forEach(function (radio) {
        radio.disabled = !HAS_SHIPPING_QUOTE;
        if (!HAS_SHIPPING_QUOTE) radio.checked = false;
        radio.closest('label')?.classList.toggle('is-selected', HAS_SHIPPING_QUOTE && radio.checked);
    });

    var submitBtn = document.getElementById('checkout-submit-btn');
    if (submitBtn) {
        submitBtn.disabled = !HAS_SHIPPING_QUOTE;
        submitBtn.innerHTML = HAS_SHIPPING_QUOTE
            ? '<i class="fa-solid fa-lock"></i> Confirmar pedido'
            : '<i class="fa-solid fa-truck-fast"></i> Calcule o frete';
    }

    if (!HAS_SHIPPING_QUOTE) {
        var shippingEl = document.getElementById('sidebar-shipping');
        var totalEl = document.getElementById('sidebar-total');
        var msgEl = document.getElementById('shipping-msg');

        if (shippingEl) shippingEl.textContent = 'A calcular';
        if (totalEl) totalEl.textContent = money(Math.max(0, SUBTOTAL - DISCOUNT));
        if (msgEl) msgEl.textContent = 'Informe o CEP para calcular o frete dos Correios.';
    }
}

function updateRateCards(rates) {
    Object.keys(rates || {}).forEach(function (service) {
        var rate = rates[service];
        var nameEl = document.querySelector('[data-rate-name="' + service + '"]');
        var etaEl = document.querySelector('[data-rate-eta="' + service + '"]');
        var priceEl = document.querySelector('[data-rate-price="' + service + '"]');

        if (nameEl) nameEl.textContent = rate.name;
        if (etaEl) etaEl.textContent = rate.eta;
        if (priceEl) priceEl.textContent = rate.is_free ? 'Gratis' : money(rate.price);
    });
}

function setShippingLoading(isLoading) {
    document.querySelectorAll('input[name="shipping_method"]').forEach(function (radio) {
        radio.disabled = isLoading;
        radio.closest('label')?.classList.toggle('is-loading', isLoading);
    });

    var sourceEl = document.getElementById('shipping-source-note');
    if (sourceEl && isLoading) sourceEl.textContent = 'Buscando cotacao em tempo real...';
}

function quoteShipping() {
    var postcode = document.getElementById('shipping_postcode')?.value?.trim() || '';
    var postcodeDigits = onlyDigits(postcode);
    var country = document.getElementById('shipping_country')?.value?.trim() || 'BR';
    var city = document.getElementById('shipping_city')?.value?.trim() || '';
    var address1 = document.getElementById('shipping_address_line1')?.value?.trim() || '';
    var address2 = document.getElementById('shipping_address_line2')?.value?.trim() || '';
    var couponCode = document.getElementById('coupon_code')?.value?.trim() || '';
    var firstName = document.getElementById('customer_first_name')?.value?.trim() || '';
    var lastName = document.getElementById('customer_last_name')?.value?.trim() || '';
    var customerName = (firstName + ' ' + lastName).trim();
    var customerEmail = document.getElementById('customer_email')?.value?.trim() || '';
    var customerPhone = document.getElementById('customer_phone')?.value?.trim() || '';
    var sourceEl = document.getElementById('shipping-source-note');

    if (postcodeDigits.length !== 8) {
        setShippingReady(false);
        if (sourceEl) sourceEl.textContent = 'Informe o CEP para buscar a cotacao real.';
        return;
    }

    setShippingLoading(true);

    fetch(QUOTE_URL, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({
            shipping_postcode: postcode,
            shipping_country: country,
            shipping_city: city,
            shipping_address_line1: address1,
            shipping_address_line2: address2,
            customer_name: customerName,
            customer_email: customerEmail,
            customer_phone: customerPhone,
            coupon_code: couponCode,
        }),
    })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) throw new Error(data.message || 'Nao foi possivel calcular o frete.');
                return data;
            });
        })
        .then(function (data) {
            RATES = data.rates || {};
            updateRateCards(RATES);

            var current = selectedShippingService();
            var selected = RATES[current] ? current : 'pac';
            var rate = RATES[selected] || null;
            DISCOUNT = Number(data.discount || 0);
            updateDiscount(data.coupon_valid, data.discount_label);

            if (rate) {
                setShippingReady(true);
                var radio = document.querySelector('input[name="shipping_method"][value="' + selected + '"]');
                if (radio) radio.checked = true;
                selectShipping(selected, Number(rate.price), !!rate.is_free);
            } else {
                setShippingReady(false);
            }

            var sourceEl = document.getElementById('shipping-source-note');
            if (sourceEl) {
                sourceEl.textContent = data.source === 'api'
                    ? 'Frete calculado pelos Correios usando o CEP informado.'
                    : (data.has_api
                        ? 'API indisponivel no momento. Exibindo estimativa por peso.'
                        : 'Frete calculado pela tabela de envio da loja.');
            }
        })
        .catch(function (error) {
            setShippingReady(false);
            var sourceEl = document.getElementById('shipping-source-note');
            if (sourceEl) sourceEl.textContent = error.message || 'Nao foi possivel calcular o frete agora.';
        })
        .finally(function () {
            setShippingLoading(false);
        });
}

function debounceQuote() {
    clearTimeout(quoteTimer);
    quoteTimer = setTimeout(quoteShipping, 500);
}

document.getElementById('shipping_postcode')?.addEventListener('input', debounceQuote);
document.getElementById('cep-search-btn')?.addEventListener('click', function () {
    lookupCep(true);
});
document.getElementById('shipping_address_line1')?.addEventListener('blur', debounceQuote);
document.getElementById('shipping_city')?.addEventListener('blur', debounceQuote);
document.getElementById('shipping_country')?.addEventListener('change', debounceQuote);
document.getElementById('coupon-apply-btn')?.addEventListener('click', quoteShipping);
document.getElementById('coupon_code')?.addEventListener('change', quoteShipping);

document.querySelectorAll('input[name="shipping_method"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var rate = RATES[radio.value];
        if (rate) selectShipping(radio.value, Number(rate.price), !!rate.is_free);
    });
});

document.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.querySelectorAll('input[name="payment_method"]').forEach(function (item) {
            item.closest('label')?.classList.toggle('is-selected', item.checked);
        });
    });
});

setShippingReady(HAS_SHIPPING_QUOTE);
var initialRate = HAS_SHIPPING_QUOTE ? (RATES[selectedShippingService()] || RATES.pac) : null;
if (initialRate) selectShipping(selectedShippingService(), Number(initialRate.price), !!initialRate.is_free);

function updateDiscount(isValid, discountLabel) {
    var row = document.getElementById('discount-row');
    var discountEl = document.getElementById('sidebar-discount');
    var couponNote = document.getElementById('coupon-note');
    var hasCoupon = (document.getElementById('coupon_code')?.value || '').trim() !== '';

    if (row) row.style.display = DISCOUNT > 0 ? '' : 'none';
    if (discountEl) discountEl.textContent = discountLabel || money(DISCOUNT);

    if (couponNote) {
        couponNote.textContent = !hasCoupon ? '' : (isValid ? 'Cupom aplicado.' : 'Cupom invalido ou expirado.');
    }
}

function onlyDigits(value) {
    return String(value || '').replace(/\D+/g, '');
}

function formatCep(value) {
    var digits = onlyDigits(value).slice(0, 8);
    return digits.length > 5 ? digits.slice(0, 5) + '-' + digits.slice(5) : digits;
}

function lookupCep(force) {
    var cepInput = document.getElementById('shipping_postcode');
    if (!cepInput) return;

    cepInput.value = formatCep(cepInput.value);
    var cep = onlyDigits(cepInput.value);
    if (cep.length !== 8) return;
    if (!force && lookupCep.lastCep === cep) return;
    lookupCep.lastCep = cep;

    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.erro) throw new Error('CEP nao encontrado.');

            var address = document.getElementById('shipping_address_line1');
            var neighborhood = document.getElementById('shipping_neighborhood');
            var city = document.getElementById('shipping_city');
            var state = document.getElementById('shipping_state');

            if (address && data.logradouro) address.value = data.logradouro;
            if (neighborhood && data.bairro) neighborhood.value = data.bairro;
            if (city && data.localidade) city.value = data.localidade;
            if (state && data.uf) state.value = data.uf;
            quoteShipping();
        })
        .catch(function (error) {
            var sourceEl = document.getElementById('shipping-source-note');
            if (sourceEl) sourceEl.textContent = error.message || 'Nao foi possivel buscar o CEP.';
        });
}

document.getElementById('shipping_postcode')?.addEventListener('input', function () {
    this.value = formatCep(this.value);
    if (onlyDigits(this.value).length !== 8) setShippingReady(false);
    if (onlyDigits(this.value).length === 8) lookupCep(false);
});

(function () {
    var CAPTURE_URL = '{{ route('checkout.capture-cart') }}';
    var captureTimer = null;
    var lastCapturedEmail = '';

    function captureCartData() {
        var email = (document.getElementById('customer_email')?.value || '').trim();
        if (!email || email === lastCapturedEmail) return;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return;

        lastCapturedEmail = email;

        fetch(CAPTURE_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({
                customer_first_name: (document.getElementById('customer_first_name')?.value || '').trim(),
                customer_last_name: (document.getElementById('customer_last_name')?.value || '').trim(),
                customer_email: email,
                customer_phone: (document.getElementById('customer_phone')?.value || '').trim(),
            }),
        }).catch(function () {});
    }

    function debouncedCapture() {
        clearTimeout(captureTimer);
        captureTimer = setTimeout(captureCartData, 1500);
    }

    ['customer_email', 'customer_first_name', 'customer_last_name', 'customer_phone'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('blur', debouncedCapture);
            el.addEventListener('change', debouncedCapture);
        }
    });
})();

(function () {
    var PROCESS_URL = '{{ route('checkout.process') }}';
    var checkoutForm = document.getElementById('checkout-form');
    var submitBtn = document.getElementById('checkout-submit-btn');

    if (!checkoutForm) return;

    checkoutForm.addEventListener('submit', function (event) {
        var selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected || selected.value !== 'mercadopago') return;

        event.preventDefault();
        trackAddPaymentInfo(selected.value);
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processando...';

        fetch(PROCESS_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF,
            },
            body: new FormData(checkoutForm),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        if (data.errors) {
                            var messages = [];
                            Object.keys(data.errors).forEach(function (key) {
                                messages = messages.concat(data.errors[key]);
                            });
                            throw new Error(messages.join('\n'));
                        }
                        throw new Error(data.error || data.message || 'Erro ao processar pedido.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }
                throw new Error('URL de pagamento nao fornecida.');
            })
            .catch(function (error) {
                alert(error.message || 'Erro ao processar pagamento.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Confirmar pedido';
            });
    });
})();
</script>
@endpush
