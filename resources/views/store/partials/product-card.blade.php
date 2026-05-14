@php
    $productCardClass = $productCardClass ?? '';
    $fallbackImage = asset('motoacessorios/placeholder-product.svg');
    $image = $product->image ?: $fallbackImage;
    $isOutOfStock = ($product->track_stock ?? true) && $product->stock <= 0;
    $installment = ((float) $product->price) / 2;
@endphp

<article class="product-card {{ $productCardClass }}">
    <a href="{{ route('store.show', $product->slug) }}">
        <img src="{{ $image }}" alt="{{ $product->name }}">
    </a>
    <span class="cat">{{ mb_strtoupper($product->category?->name ?? 'PRODUTO') }}</span>
    <h3>{{ $product->name }}</h3>
    <p class="rating">★★★★★</p>
    <strong>R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong>
    <p class="installments">2x de R$ {{ number_format($installment, 2, ',', '.') }} sem juros</p>
    <a class="buy-btn" href="{{ route('store.show', $product->slug) }}">
        <i class="fa-solid {{ $isOutOfStock ? 'fa-bell' : 'fa-cart-plus' }}"></i> {{ $isOutOfStock ? 'AVISE-ME' : 'COMPRAR' }}
    </a>
</article>
