@extends('layouts.app')

@section('title', $product->name . ' - ' . config('app.name'))
@section('bodyClass', 'page-product')

@section('content')
@php
    $fallbackImage = asset('motoacessorios/placeholder-product.svg');
    $galleryImages = collect(array_merge([$product->image], $product->gallery ?? []))
        ->filter()
        ->unique()
        ->values()
        ->all();

    if (empty($galleryImages)) {
        $galleryImages = [$fallbackImage];
    }

    $sizeOptions = collect($product->sizes ?? [])
        ->concat(collect($product->variants ?? [])->pluck('size'))
        ->filter(fn ($size) => is_string($size) && trim($size) !== '')
        ->unique(fn ($size) => mb_strtolower(trim((string) $size)))
        ->values()
        ->all();

    $colorOptions = collect($product->colors ?? [])
        ->concat(collect($product->variants ?? [])->pluck('color'))
        ->filter(fn ($color) => is_string($color) && trim($color) !== '')
        ->unique(fn ($color) => mb_strtolower(trim((string) $color)))
        ->values()
        ->all();

    $variantStockMap = collect($product->variants ?? [])
        ->mapWithKeys(fn ($variant) => [
            trim((string) $variant->size).'|'.trim((string) $variant->color) => (int) $variant->stock,
        ])
        ->all();

    $hasVariantStock = ($product->track_stock ?? true) && ! empty($variantStockMap);
    $firstAvailableVariant = $hasVariantStock
        ? collect($product->variants ?? [])->first(fn ($variant) => (int) $variant->stock > 0)
        : null;

    $selectedSize = $firstAvailableVariant
        ? trim((string) $firstAvailableVariant->size)
        : ($sizeOptions[0] ?? null);
    $selectedColor = $firstAvailableVariant
        ? trim((string) $firstAvailableVariant->color)
        : ($colorOptions[0] ?? null);

    $selectedVariantKey = trim((string) ($selectedSize ?? '')).'|'.trim((string) ($selectedColor ?? ''));
    $selectedVariantStock = $hasVariantStock
        ? (int) ($variantStockMap[$selectedVariantKey] ?? 0)
        : (int) ($product->stock ?? 0);
    $isInStock = ! ($product->track_stock ?? true) || $selectedVariantStock > 0;
    $installment = ((float) $product->price) / 2;
@endphp

<section class="product-detail-page">
    <div class="container">
        <a class="product-back-link" href="{{ route('store.index') }}">
            <i class="fa-solid fa-arrow-left"></i> Voltar para loja
        </a>

        <div class="product-detail-grid">
            <div class="product-media">
                <div class="product-gallery {{ count($galleryImages) > 1 ? 'has-thumbs' : '' }}">
                    @if(count($galleryImages) > 1)
                        <div class="product-thumbs">
                            @foreach($galleryImages as $index => $image)
                                <button
                                    type="button"
                                    class="product-thumb {{ $index === 0 ? 'is-active' : '' }}"
                                    data-index="{{ $index }}"
                                    aria-label="Ver imagem {{ $index + 1 }}"
                                >
                                    <img src="{{ $image }}" alt="{{ $product->name }}">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="product-image-stage" id="productImageStage">
                        <img id="productMainImage" src="{{ $galleryImages[0] }}" alt="{{ $product->name }}">

                        @if(count($galleryImages) > 1)
                            <button type="button" class="product-gallery-arrow product-gallery-arrow--prev" id="galleryPrev" aria-label="Imagem anterior">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" class="product-gallery-arrow product-gallery-arrow--next" id="galleryNext" aria-label="Proxima imagem">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <aside class="product-sidebar">
                <div class="product-info-card">
                    <p class="product-kicker">{{ $product->category?->name ?? 'Produto' }}</p>
                    <h1>{{ $product->name }}</h1>

                    <div class="product-price-block">
                        <strong>R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong>
                        <span>2x de R$ {{ number_format($installment, 2, ',', '.') }} sem juros</span>
                    </div>

                    <div class="description-html product-description">
                        {!! $product->description ?: 'Produto selecionado com qualidade, acabamento resistente e uso pensado para motociclistas.' !!}
                    </div>

                    <div id="productStockInfo" class="product-stock {{ ($product->track_stock ?? true) && ! $isInStock ? 'hidden' : '' }}">
                        <span>Estoque:</span>
                        <strong id="productStockBadge" class="{{ $isInStock ? 'is-available' : 'is-empty' }}">
                            {{ ($product->track_stock ?? true) ? ($selectedVariantStock > 0 ? $selectedVariantStock . ' disponivel' : 'esgotado') : 'infinito' }}
                        </strong>
                    </div>

                    <form id="addToCartForm" action="{{ route('cart.add', $product) }}" method="POST" class="product-buy-form">
                        @csrf
                        <input type="hidden" name="size" id="selectedSize" value="{{ $selectedSize }}">
                        <input type="hidden" name="color" id="selectedColor" value="{{ $selectedColor }}">

                        @if(! empty($sizeOptions))
                            <div class="product-option-group">
                                <p>Tamanho</p>
                                <div class="product-option-grid">
                                    @foreach($sizeOptions as $size)
                                        <button
                                            type="button"
                                            class="size-option {{ $size === $selectedSize ? 'is-selected' : '' }}"
                                            data-size="{{ $size }}"
                                        >{{ $size }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(! empty($colorOptions))
                            <div class="product-option-group">
                                <p>Cor</p>
                                <div class="product-color-list">
                                    @foreach($colorOptions as $color)
                                        <button
                                            type="button"
                                            class="color-option {{ $color === $selectedColor ? 'is-selected' : '' }}"
                                            data-color="{{ $color }}"
                                        >{{ $color }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="product-quantity">
                            <label for="quantity">Quantidade</label>
                            <div class="quantity-stepper" data-quantity-stepper>
                                <button type="button" class="quantity-stepper__btn" data-quantity-action="decrease" aria-label="Diminuir quantidade">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input id="quantity" type="number" name="quantity" min="1" max="{{ ($product->track_stock ?? true) ? max(1, min(10, $selectedVariantStock)) : 10 }}" value="1" inputmode="numeric">
                                <button type="button" class="quantity-stepper__btn" data-quantity-action="increase" aria-label="Aumentar quantidade">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="addToCartBtn" class="product-add-button" {{ $isInStock ? '' : 'disabled' }}>
                            {{ $isInStock ? 'ADICIONAR AO CARRINHO' : 'PRODUTO ESGOTADO' }}
                        </button>
                    </form>

                    <div class="product-accordions">
                        <details>
                            <summary>Observações <span>+</span></summary>
                            <div class="description-html">{!! $product->observations ?: 'Sem observacoes adicionais para este produto.' !!}</div>
                        </details>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

@if(($relatedProducts ?? collect())->isNotEmpty())
    <section class="related-products-section">
        <div class="container">
            <div class="section-head related-products-head">
                <div>
                    <p class="page-kicker">Descubra mais</p>
                    <h2>Itens relacionados</h2>
                </div>
                <div class="related-products-controls" aria-label="Controles do carrossel">
                    <button type="button" class="related-carousel-arrow related-carousel-prev" aria-label="Produto anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="related-carousel-arrow related-carousel-next" aria-label="Proximo produto">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="related-carousel" data-related-carousel>
                <div class="related-carousel-track">
                    @foreach($relatedProducts as $related)
                        @include('store.partials.product-card', ['product' => $related, 'productCardClass' => 'related-carousel-card'])
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

<div id="cartSuccessModal" class="cart-modal" aria-hidden="true">
    <div class="cart-modal__panel" role="dialog" aria-modal="true" aria-label="Produto adicionado ao carrinho">
        <div class="cart-modal__head">
            <span><i class="fa-solid fa-check"></i></span>
            <strong>Item adicionado</strong>
            <button type="button" id="cartModalClose" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="cart-modal__body">
            <img id="csm-image" src="" alt="">
            <div>
                <h3 id="csm-name"></h3>
                <p id="csm-price"></p>
                <small id="csm-count"></small>
            </div>
        </div>
        <div class="cart-modal__actions">
            <button type="button" id="cartModalContinue">Continuar comprando</button>
            <a id="csm-checkout" href="{{ route('cart.index') }}">Finalizar compra</a>
        </div>
    </div>
</div>

<div id="cartToast" class="cart-toast"></div>

<div id="productImageLightbox" class="product-lightbox" aria-hidden="true">
    <div class="product-lightbox__panel" role="dialog" aria-modal="true" aria-label="Imagem ampliada do produto">
        <button type="button" class="product-lightbox__close" id="productLightboxClose" aria-label="Fechar imagem">
            <i class="fa-solid fa-xmark"></i>
        </button>

        @if(count($galleryImages) > 1)
            <button type="button" class="product-lightbox__arrow product-lightbox__arrow--prev" id="productLightboxPrev" aria-label="Imagem anterior">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        @endif

        <img id="productLightboxImage" src="{{ $galleryImages[0] }}" alt="{{ $product->name }}">

        @if(count($galleryImages) > 1)
            <button type="button" class="product-lightbox__arrow product-lightbox__arrow--next" id="productLightboxNext" aria-label="Proxima imagem">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        @endif
    </div>
</div>

<script>
const productGalleryImages = @json($galleryImages);
let currentGalleryIndex = 0;
const productTrackStock = @json((bool) ($product->track_stock ?? true));
const productHasVariantStock = @json($hasVariantStock);
const productVariantStockMap = @json($variantStockMap);
const productFallbackStock = @json((int) ($product->stock ?? 0));
const productPixelEnabled = @json((bool) config('store.pixel.facebook'));
const productPixelData = {
    id: @json((string) $product->id),
    name: @json($product->name),
    price: @json((float) $product->price),
    currency: @json(strtoupper((string) config('store.pixel.facebook_currency', 'BRL'))),
};

function currentVariantKey() {
    const size = document.getElementById('selectedSize')?.value || '';
    const color = document.getElementById('selectedColor')?.value || '';
    return `${String(size).trim()}|${String(color).trim()}`;
}

function resolveCurrentStock() {
    if (!productTrackStock) return null;
    if (productHasVariantStock) return Number(productVariantStockMap[currentVariantKey()] || 0);
    return Number(productFallbackStock || 0);
}

function hasAvailableVariant(size, color) {
    if (!productHasVariantStock) return true;

    const normalizedSize = String(size || '').trim();
    const normalizedColor = String(color || '').trim();

    return Object.entries(productVariantStockMap || {}).some(([key, stock]) => {
        if (Number(stock || 0) <= 0) return false;
        const [keySize = '', keyColor = ''] = key.split('|');
        return (!normalizedSize || keySize.trim() === normalizedSize)
            && (!normalizedColor || keyColor.trim() === normalizedColor);
    });
}

function syncOptions() {
    if (!productHasVariantStock) return;

    const selectedSize = String(document.getElementById('selectedSize')?.value || '').trim();
    const selectedColorInput = document.getElementById('selectedColor');
    let selectedColor = String(selectedColorInput?.value || '').trim();

    document.querySelectorAll('.color-option').forEach((button) => {
        const color = String(button.dataset.color || '').trim();
        button.hidden = !hasAvailableVariant(selectedSize, color);
    });

    const visibleColors = Array.from(document.querySelectorAll('.color-option')).filter((button) => !button.hidden);
    const currentColorVisible = visibleColors.some((button) => String(button.dataset.color || '').trim() === selectedColor);

    if (!currentColorVisible && visibleColors.length && selectedColorInput) {
        selectedColor = String(visibleColors[0].dataset.color || '').trim();
        selectedColorInput.value = selectedColor;
    }

    document.querySelectorAll('.size-option').forEach((button) => {
        button.classList.toggle('is-selected', String(button.dataset.size || '').trim() === selectedSize);
    });
    document.querySelectorAll('.color-option').forEach((button) => {
        button.classList.toggle('is-selected', String(button.dataset.color || '').trim() === selectedColor);
    });
}

function syncStockUi() {
    const badge = document.getElementById('productStockBadge');
    const stockInfo = document.getElementById('productStockInfo');
    const quantityInput = document.getElementById('quantity');
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (!badge || !quantityInput || !addToCartBtn) return;

    if (!productTrackStock) {
        stockInfo?.classList.remove('hidden');
        badge.textContent = 'infinito';
        badge.className = 'is-available';
        quantityInput.max = 10;
        clampQuantity();
        addToCartBtn.disabled = false;
        addToCartBtn.textContent = 'ADICIONAR AO CARRINHO';
        return;
    }

    const stock = resolveCurrentStock();
    const inStock = stock > 0;
    stockInfo?.classList.toggle('hidden', !inStock);
    badge.textContent = inStock ? `${stock} disponivel` : 'esgotado';
    badge.className = inStock ? 'is-available' : 'is-empty';
    quantityInput.max = inStock ? Math.max(1, Math.min(10, stock)) : 1;
    clampQuantity();
    addToCartBtn.disabled = !inStock;
    addToCartBtn.textContent = inStock ? 'ADICIONAR AO CARRINHO' : 'PRODUTO ESGOTADO';
}

function clampQuantity() {
    const quantityInput = document.getElementById('quantity');
    if (!quantityInput) return;

    const min = Number(quantityInput.min || 1);
    const max = Number(quantityInput.max || 10);
    let value = Number.parseInt(quantityInput.value, 10);

    if (Number.isNaN(value)) value = min;
    value = Math.max(min, Math.min(max, value));
    quantityInput.value = value;

    document.querySelectorAll('[data-quantity-action="decrease"]').forEach((button) => {
        button.disabled = value <= min;
    });
    document.querySelectorAll('[data-quantity-action="increase"]').forEach((button) => {
        button.disabled = value >= max;
    });
}

function showGalleryImage(index) {
    if (!productGalleryImages.length) return;
    if (index < 0) index = productGalleryImages.length - 1;
    if (index >= productGalleryImages.length) index = 0;
    currentGalleryIndex = index;

    const main = document.getElementById('productMainImage');
    if (main) {
        main.style.opacity = '0.45';
        main.src = productGalleryImages[index];
        setTimeout(() => { main.style.opacity = '1'; }, 120);
    }

    document.querySelectorAll('.product-thumb').forEach((thumb, thumbIndex) => {
        thumb.classList.toggle('is-active', thumbIndex === index);
    });

    const lightboxImage = document.getElementById('productLightboxImage');
    if (lightboxImage) lightboxImage.src = productGalleryImages[index];
}

function openProductLightbox(index = currentGalleryIndex) {
    const lightbox = document.getElementById('productImageLightbox');
    const lightboxImage = document.getElementById('productLightboxImage');
    if (!lightbox || !lightboxImage || !productGalleryImages.length) return;

    showGalleryImage(index);
    lightboxImage.src = productGalleryImages[currentGalleryIndex];
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.classList.add('lightbox-open');
}

function closeProductLightbox() {
    const lightbox = document.getElementById('productImageLightbox');
    if (!lightbox) return;
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('lightbox-open');
}

function openCartModal(data) {
    const modal = document.getElementById('cartSuccessModal');
    if (!modal) return;
    document.getElementById('csm-image').src = data.product_image || '';
    document.getElementById('csm-name').textContent = data.product_name || '';
    document.getElementById('csm-price').textContent = data.product_price || '';
    document.getElementById('csm-count').textContent = `${data.cart_count} ${data.cart_count === 1 ? 'item' : 'itens'} na sacola`;
    document.getElementById('csm-checkout').href = data.cart_url || '{{ route('cart.index') }}';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeCartModal() {
    const modal = document.getElementById('cartSuccessModal');
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

function showCartToast(message, isError) {
    const toast = document.getElementById('cartToast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.toggle('is-error', Boolean(isError));
    toast.classList.add('is-visible');
    setTimeout(() => toast.classList.remove('is-visible'), 3200);
}

function trackProductAddToCart(quantity) {
    if (!productPixelEnabled || typeof fbq !== 'function') return;

    const qty = Math.max(1, Number(quantity || 1));
    fbq('track', 'AddToCart', {
        content_ids: [productPixelData.id],
        content_name: productPixelData.name,
        content_type: 'product',
        contents: [{ id: productPixelData.id, quantity: qty }],
        value: Number((productPixelData.price * qty).toFixed(2)),
        currency: productPixelData.currency,
    });
}

document.querySelectorAll('.product-thumb').forEach((thumb) => {
    thumb.addEventListener('click', () => showGalleryImage(Number(thumb.dataset.index || 0)));
});
document.getElementById('galleryPrev')?.addEventListener('click', () => showGalleryImage(currentGalleryIndex - 1));
document.getElementById('galleryNext')?.addEventListener('click', () => showGalleryImage(currentGalleryIndex + 1));
document.getElementById('productMainImage')?.addEventListener('click', () => openProductLightbox());
document.getElementById('productLightboxClose')?.addEventListener('click', closeProductLightbox);
document.getElementById('productLightboxPrev')?.addEventListener('click', () => showGalleryImage(currentGalleryIndex - 1));
document.getElementById('productLightboxNext')?.addEventListener('click', () => showGalleryImage(currentGalleryIndex + 1));
document.getElementById('productImageLightbox')?.addEventListener('click', (event) => {
    if (event.target.id === 'productImageLightbox') closeProductLightbox();
});

document.querySelectorAll('.size-option').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('selectedSize').value = button.dataset.size || '';
        syncOptions();
        syncStockUi();
    });
});
document.querySelectorAll('.color-option').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('selectedColor').value = button.dataset.color || '';
        syncOptions();
        syncStockUi();
    });
});

document.querySelectorAll('[data-quantity-action]').forEach((button) => {
    button.addEventListener('click', () => {
        const quantityInput = document.getElementById('quantity');
        if (!quantityInput) return;

        const direction = button.dataset.quantityAction === 'increase' ? 1 : -1;
        quantityInput.value = Number(quantityInput.value || 1) + direction;
        clampQuantity();
    });
});

document.getElementById('quantity')?.addEventListener('input', clampQuantity);
document.getElementById('quantity')?.addEventListener('blur', clampQuantity);

document.getElementById('addToCartForm')?.addEventListener('submit', (event) => {
    event.preventDefault();
    clampQuantity();
    const form = event.currentTarget;
    const button = document.getElementById('addToCartBtn');
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Adicionando...';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: new FormData(form),
    })
        .then((response) => response.json())
        .then((json) => {
            button.disabled = false;
            button.textContent = originalText;
            if (!json.success) {
                showCartToast(json.message || 'Erro ao adicionar.', true);
                return;
            }

            const badge = document.getElementById('cart-badge');
            if (badge) badge.textContent = json.cart_count;
            trackProductAddToCart(form.querySelector('[name="quantity"]')?.value || 1);
            openCartModal(json);
        })
        .catch(() => {
            button.disabled = false;
            button.textContent = originalText;
            showCartToast('Ocorreu um erro. Tente novamente.', true);
        });
});

document.getElementById('cartModalClose')?.addEventListener('click', closeCartModal);
document.getElementById('cartModalContinue')?.addEventListener('click', closeCartModal);
document.getElementById('cartSuccessModal')?.addEventListener('click', (event) => {
    if (event.target.id === 'cartSuccessModal') closeCartModal();
});
document.addEventListener('keydown', (event) => {
    const lightbox = document.getElementById('productImageLightbox');
    const lightboxOpen = lightbox?.classList.contains('is-open');

    if (event.key === 'Escape') {
        closeCartModal();
        closeProductLightbox();
    }

    if (!lightboxOpen) return;
    if (event.key === 'ArrowLeft') showGalleryImage(currentGalleryIndex - 1);
    if (event.key === 'ArrowRight') showGalleryImage(currentGalleryIndex + 1);
});

const relatedCarousel = document.querySelector('[data-related-carousel]');
if (relatedCarousel) {
    const track = relatedCarousel.querySelector('.related-carousel-track');
    const cards = track ? Array.from(track.querySelectorAll('.related-carousel-card')) : [];
    const prev = document.querySelector('.related-carousel-prev');
    const next = document.querySelector('.related-carousel-next');
    let relatedIndex = 0;
    let relatedTimer;

    const visibleRelatedCards = () => {
        if (window.innerWidth <= 680) return 1;
        if (window.innerWidth <= 1050) return 2;
        return 4;
    };

    const relatedStep = () => {
        const firstCard = cards[0];
        if (!firstCard || !track) return 0;
        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap || 0);
        return firstCard.getBoundingClientRect().width + gap;
    };

    const maxRelatedIndex = () => Math.max(0, cards.length - visibleRelatedCards());

    const goToRelated = (index, smooth = true) => {
        if (!track || !cards.length) return;
        relatedIndex = Math.max(0, Math.min(index, maxRelatedIndex()));
        track.style.transition = smooth ? 'transform .45s ease' : 'none';
        track.style.transform = `translate3d(-${relatedIndex * relatedStep()}px, 0, 0)`;
        if (prev) prev.disabled = relatedIndex === 0;
        if (next) next.disabled = relatedIndex === maxRelatedIndex();
    };

    const startRelatedAuto = () => {
        clearInterval(relatedTimer);
        if (cards.length <= visibleRelatedCards()) return;
        relatedTimer = setInterval(() => {
            goToRelated(relatedIndex >= maxRelatedIndex() ? 0 : relatedIndex + 1);
        }, 3200);
    };

    prev?.addEventListener('click', () => {
        goToRelated(relatedIndex - 1);
        startRelatedAuto();
    });
    next?.addEventListener('click', () => {
        goToRelated(relatedIndex + 1);
        startRelatedAuto();
    });
    relatedCarousel.addEventListener('mouseenter', () => clearInterval(relatedTimer));
    relatedCarousel.addEventListener('mouseleave', startRelatedAuto);
    window.addEventListener('resize', () => {
        goToRelated(relatedIndex, false);
        startRelatedAuto();
    });

    goToRelated(0, false);
    startRelatedAuto();
}

syncOptions();
syncStockUi();
</script>
@endsection

@push('pixel_events')
@if(config('store.pixel.facebook'))
<script>
if (typeof fbq === 'function') {
    fbq('track', 'ViewContent', {
        content_ids: ['{{ $product->id }}'],
        content_name: @json($product->name),
        content_type: 'product',
        value: {{ (float) $product->price }},
        currency: '{{ strtoupper((string) config('store.pixel.facebook_currency', 'BRL')) }}'
    });
}
</script>
@endif
@endpush
