@extends('layouts.app')
@section('title', 'Moto Acessorios - Equipamentos e pecas para motociclistas')
@section('bodyClass', 'page-home')
@section('content')
@php
    $featured = $products->where('highlight_best_sellers', true)->take(10)->values();
    if ($featured->isEmpty()) {
        $featured = $products->where('featured', true)->take(10)->values();
    }
    if ($featured->isEmpty()) {
        $featured = $products->take(10)->values();
    }

    $categoryImages = [
        'capacetes' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png',
        'pecas' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png',
        'eletrica' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
        'vestuario' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png',
        'acessorios' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/moto.png',
    ];
    $preferredSlugs = ['capacetes', 'pecas', 'eletrica', 'vestuario', 'acessorios'];
    $homeCategories = collect($preferredSlugs)
        ->map(fn ($slug) => $categories->firstWhere('slug', $slug))
        ->filter()
        ->values();
    if ($homeCategories->isEmpty()) {
        $homeCategories = $categories->take(5);
    }
@endphp

<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <h1>EQUIPE-SE PARA A <span>ESTRADA</span></h1>
            <p>Os melhores acessorios para sua moto com qualidade, seguranca e preco justo.</p>
            <div class="hero-cta">
                <a href="#produtos" class="btn btn-green"><i class="fa-solid fa-bag-shopping"></i> VER PRODUTOS</a>
                <a href="{{ config('app.contact.whatsapp_url', '#') }}" class="btn btn-outline"><i class="fa-brands fa-whatsapp"></i> CHAMAR NO WHATSAPP</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=1200&q=80" alt="Piloto em moto esportiva">
        </div>
    </div>
</section>

<section class="benefits">
    <div class="container benefits-grid">
        <article><i class="fa-solid fa-truck-fast"></i><div><h4>Envio rapido</h4><p>Postamos no mesmo dia</p></div></article>
        <article><i class="fa-solid fa-arrows-rotate"></i><div><h4>Ate 2x sem juros</h4><p>Parcele com seguranca</p></div></article>
        <article><i class="fa-solid fa-lock"></i><div><h4>Compra segura</h4><p>Seus dados protegidos</p></div></article>
        <article><i class="fa-solid fa-percent"></i><div><h4>5% OFF no PIX</h4><p>Desconto automatico</p></div></article>
    </div>
</section>

<section class="categories">
    <div class="container">
        <h2>CATEGORIAS</h2>
        <div class="categories-slider" data-slider="categories">
            <button class="categories-arrow categories-prev" type="button" aria-label="Categoria anterior">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="categories-viewport">
                <div class="categories-track">
                    @foreach($homeCategories as $category)
                        <a href="{{ route('store.category', $category->slug) }}" @class(['cat-item reveal visible', 'active' => $activeCategory === $category->slug])>
                            <div class="cat-item__circle">
                                <img src="{{ $category->image ?: ($categoryImages[$category->slug] ?? 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png') }}" alt="{{ $category->name }}">
                            </div>
                            <span>{{ mb_strtoupper($category->name) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <button class="categories-arrow categories-next" type="button" aria-label="Proxima categoria">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<section class="products" id="produtos">
    <div class="container">
        <div class="section-head">
            <h2>DESTAQUE DA SEMANA</h2>
            <a href="{{ route('store.search') }}">VER TODOS</a>
        </div>

        @if($featured->isEmpty())
            <div class="empty-state"><h2>Nenhum produto cadastrado</h2><p>Cadastre produtos no painel para exibir aqui.</p></div>
        @else
            <div class="product-grid">
                @foreach($featured as $product)
                    @include('store.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="promo-row">
    <div class="container promo-grid">
        <article>
            <div class="promo-copy">
                <h3>PAGUE NO PIX <span>E GANHE 5% OFF</span></h3>
                <p>Desconto aplicado automaticamente</p>
            </div>
            <div class="promo-icon" aria-hidden="true"><i class="fa-brands fa-pix"></i></div>
        </article>
        <article>
            <div class="promo-copy">
                <h3>PRECISOU? <span>FALE CONOSCO!</span></h3>
                <p>Atendimento rapido pelo WhatsApp</p>
            </div>
            <div class="promo-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></div>
        </article>
    </div>
</section>

<section class="proof">
    <div class="container proof-grid">
        <article><i class="fa-regular fa-calendar"></i><div><h4>Desde 1994</h4><p>Realizando sonhos</p></div></article>
        <article><i class="fa-regular fa-user"></i><div><h4>+10 mil clientes</h4><p>Satisfeitos</p></div></article>
        <article><i class="fa-solid fa-shield-halved"></i><div><h4>Qualidade garantida</h4><p>Produtos originais</p></div></article>
        <article><i class="fa-solid fa-headset"></i><div><h4>Suporte especializado</h4><p>Atendimento humanizado</p></div></article>
    </div>
</section>
@endsection
