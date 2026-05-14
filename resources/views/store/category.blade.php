@extends('layouts.app')

@section('title', $category->name . ' - ' . config('app.name'))

@section('content')
<section class="catalog-hero">
    <div class="container">
        <span class="page-kicker">Categoria</span>
        <h1>{{ mb_strtoupper($category->name) }}</h1>
        <p>{{ $products->count() }} {{ $products->count() === 1 ? 'produto encontrado' : 'produtos encontrados' }}</p>
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
            <div class="product-grid">
                @foreach ($products as $product)
                    @include('store.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
