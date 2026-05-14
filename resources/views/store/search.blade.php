@extends('layouts.app')

@section('title', 'Busca - ' . config('app.name'))

@section('content')
<section class="catalog-hero">
    <div class="container">
        <span class="page-kicker">Resultado da busca</span>
        <h1>
            @if($searchQuery !== '')
                "{{ $searchQuery }}"
            @else
                BUSCAR PRODUTOS
            @endif
        </h1>
        <p>
            @if($searchQuery === '')
                Use filtros para encontrar produtos por nome, descricao ou categoria.
            @else
                {{ $products->count() }} {{ $products->count() === 1 ? 'produto encontrado' : 'produtos encontrados' }}.
            @endif
        </p>
    </div>
</section>

<section class="products">
    <div class="container search-layout">
        <aside class="search-filter">
            <h2>Refinar Busca</h2>
            <form action="{{ route('store.search') }}" method="GET">
                <div>
                    <label for="search-q">Termo</label>
                    <input id="search-q" type="text" name="q" value="{{ $searchQuery }}" placeholder="Ex.: capacete">
                </div>

                <div>
                    <label for="search-category">Categoria</label>
                    <select id="search-category" name="category">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-prices">
                    <div>
                        <label for="search-min-price">Min R$</label>
                        <input id="search-min-price" type="number" step="0.01" min="0" name="min_price" value="{{ $minPrice ?? '' }}">
                    </div>
                    <div>
                        <label for="search-max-price">Max R$</label>
                        <input id="search-max-price" type="number" step="0.01" min="0" name="max_price" value="{{ $maxPrice ?? '' }}">
                    </div>
                </div>

                <label class="checkline">
                    <input type="checkbox" name="in_stock" value="1" @checked($inStockOnly)>
                    Mostrar apenas disponiveis
                </label>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-green">Filtrar</button>
                    <a href="{{ route('store.search') }}" class="btn btn-outline">Limpar</a>
                </div>
            </form>
        </aside>

        <div>
            @if($searchQuery === '' && $activeCategory === '' && $minPrice === null && $maxPrice === null && !$inStockOnly)
                <div class="empty-state">
                    <h2>O que voce procura hoje?</h2>
                    <p>Tente por exemplo: capacete, bau, jaqueta, luva ou pneu.</p>
                </div>
            @elseif($products->isEmpty())
                <div class="empty-state">
                    <h2>Nenhum resultado encontrado</h2>
                    <p>Ajuste os filtros ou tente outro termo de busca.</p>
                    <a href="{{ route('store.index') }}" class="btn btn-green">VER TODA A LOJA</a>
                </div>
            @else
                <div class="product-grid">
                    @foreach ($products as $product)
                        @include('store.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('pixel_events')
@if(config('store.pixel.facebook') && $searchQuery !== '')
<script>
if (typeof fbq === 'function') {
    fbq('track', 'Search', { search_string: @json($searchQuery) });
}
</script>
@endif
@endpush
