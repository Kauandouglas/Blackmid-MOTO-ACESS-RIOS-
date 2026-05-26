@extends('layouts.app')

@section('title', $category->name . ' - ' . config('app.name'))

@section('content')
<section class="catalog-hero">
    <div class="container">
        <span class="page-kicker">Categoria</span>
        <h1>{{ mb_strtoupper($category->name) }}</h1>
        <p>{{ $products->total() }} {{ $products->total() === 1 ? 'produto encontrado' : 'produtos encontrados' }}</p>
    </div>
</section>

<section class="products">
    <div class="container">
        @if ($products->isEmpty())
            <div class="empty-state">
                <h2>Nenhum produto disponivel nesta categoria.</h2>
                <a href="{{ route('store.index') }}" class="btn btn-green">VER TODA A LOJA</a>
            </div>
        @else
            <div class="catalog-toolbar">
                <p>
                    Exibindo {{ $products->firstItem() }}-{{ $products->lastItem() }}
                    de {{ $products->total() }}
                </p>

                <form action="{{ route('store.category', $category->slug) }}" method="GET">
                    <label for="category-sort">Ordenar</label>
                    <select id="category-sort" name="sort" onchange="this.form.submit()">
                        <option value="relevance" @selected(($sort ?? 'relevance') === 'relevance')>Mais relevantes</option>
                        <option value="price_asc" @selected(($sort ?? 'relevance') === 'price_asc')>Menor valor</option>
                        <option value="price_desc" @selected(($sort ?? 'relevance') === 'price_desc')>Maior valor</option>
                    </select>
                </form>
            </div>

            <div class="product-grid">
                @foreach ($products as $product)
                    @include('store.partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <div class="catalog-pagination">
                {{ $products->links('store.partials.pagination') }}
            </div>
        @endif
    </div>
</section>
@endsection
