<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? config('app.name', 'Moto Acessórios'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Barlow:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('motoacessorios/style.css') }}">
    @php
        $fbPixelConfig = trim((string) config('store.pixel.facebook', ''));
        $fbPixelIsScript = str_contains($fbPixelConfig, '<script') || str_contains($fbPixelConfig, '<noscript') || str_contains($fbPixelConfig, '</script>');
        $navItems = ($navigationItems ?? collect());
        $activeCategorySlug = request()->routeIs('store.category')
            ? (string) request()->route('slug')
            : (string) request('category', '');
        $mainCategories = collect([
            ['title' => 'Capacetes', 'slug' => 'capacetes', 'key' => 'capacetes'],
            ['title' => 'Peças', 'slug' => 'pecas', 'key' => 'pecas'],
            ['title' => 'Elétrica', 'slug' => 'eletrica', 'key' => 'eletrica'],
            ['title' => 'Vestuário', 'slug' => 'vestuario', 'key' => 'vestuario'],
            ['title' => 'Acessórios', 'slug' => 'acessorios', 'key' => 'acessorios'],
        ]);
    @endphp
    @if($fbPixelConfig)
        @if($fbPixelIsScript)
            {!! trim($fbPixelConfig) !!}
        @else
            <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $fbPixelConfig }}');fbq('track','PageView');</script>
            <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $fbPixelConfig }}&ev=PageView&noscript=1"/></noscript>
        @endif
    @endif
    @stack('head_scripts')
</head>
<body class="@yield('bodyClass')">
    <div class="top-strip">
        <div class="container top-strip-inner">
            <div class="top-strip-viewport" aria-label="Vantagens da loja">
                <div class="top-strip-track" id="top-strip-track">
                    <span class="top-strip-item">5% OFF no PIX</span>
                    <span class="top-strip-item">Parcele em até 2x s/ juros</span>
                    <span class="top-strip-item">Envio rápido para todo Brasil</span>
                </div>
            </div>
        </div>
    </div>

    <header class="header">
        <div class="container header-main">
            <a class="logo" href="{{ route('store.index') }}">
                <img class="logo-img" src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Moto Acessórios') }}">
            </a>

            <form class="search" action="{{ route('store.search') }}" method="GET" data-search-suggest-url="{{ route('store.search.suggestions') }}">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="O que você procura?" autocomplete="off">
                <button type="submit" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
                <div class="search-suggestions" data-search-suggestions hidden></div>
            </form>

            <div class="header-actions">
                <a href="{{ auth()->check() ? route('store.minha-conta') : route('auth.login') }}">
                    <i class="fa-regular fa-user"></i><span>Entrar<br>Minha conta</span>
                </a>
                <a href="{{ route('cart.index') }}">
                    <i class="fa-solid fa-cart-shopping"></i><span>Carrinho</span><em class="cart-count" id="cart-badge">{{ $cartCount ?? 0 }}</em>
                </a>
            </div>

            <button class="menu-toggle" aria-label="Abrir menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <nav class="main-nav">
            <div class="mobile-nav-overlay"></div>
            <div class="container main-nav-shell">
                <div class="mobile-nav-head">
                    <a class="mobile-nav-user" href="{{ auth()->check() ? route('store.minha-conta') : route('auth.login') }}">
                        <span class="mobile-nav-avatar"><i class="fa-solid fa-user"></i></span>
                        <div>
                            <strong>{{ auth()->check() ? 'Minha conta' : 'Olá, visitante' }}</strong>
                            <small>{{ auth()->check() ? 'Ver pedidos e dados' : 'Entrar com e-mail' }}</small>
                        </div>
                    </a>
                    <button class="menu-close" aria-label="Fechar menu"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="mobile-nav-shortcuts">
                    <a href="{{ route('store.minha-conta') }}"><i class="fa-solid fa-truck-fast"></i><span>Rastrear pedido</span></a>
                    <a href="{{ route('store.contato') }}"><i class="fa-regular fa-circle-question"></i><span>Atendimento</span></a>
                </div>

                <h4 class="mobile-nav-title">Navegue por categoria</h4>

                <ul>
                    @foreach($mainCategories as $category)
                        <li>
                            <a
                                href="{{ route('store.category', $category['slug']) }}"
                                data-menu-key="{{ $category['key'] }}"
                                @class(['active' => $activeCategorySlug === $category['slug']])
                            >
                                <span>{{ $category['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>

        <div class="mega-menu" id="mega-menu">
            <div class="container mega-wrap">
                <aside class="mega-categories">
                    <h4>Categorias</h4>
                    <ul id="mega-categories-list"></ul>
                </aside>

                <section class="mega-preview">
                    <div class="mega-preview-track" id="mega-preview-track"></div>

                    <div class="mega-controls">
                        <button type="button" class="mega-arrow" id="mega-prev" aria-label="Produto anterior"><i class="fa-solid fa-chevron-left"></i></button>
                        <div class="mega-dots" id="mega-dots"></div>
                        <button type="button" class="mega-arrow" id="mega-next" aria-label="Próximo produto"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </section>
            </div>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="container alert-wrap"><div class="store-alert store-alert--success">{{ session('success') }}</div></div>
        @endif
        @if (session('error'))
            <div class="container alert-wrap"><div class="store-alert store-alert--error">{{ session('error') }}</div></div>
        @endif
        @if ($errors->any())
            <div class="container alert-wrap">
                <div class="store-alert store-alert--error">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container footer-highlights">
            <article><i class="fa-solid fa-truck-fast"></i><span>Envio rápido para todo Brasil</span></article>
            <article><i class="fa-solid fa-lock"></i><span>Compra 100% segura</span></article>
            <article><i class="fa-solid fa-credit-card"></i><span>Cartão em até 2x sem juros</span></article>
            <article><i class="fa-solid fa-headset"></i><span>Atendimento especializado</span></article>
        </div>

        <div class="container footer-grid">
            <div class="footer-brand">
                <a class="logo" href="{{ route('store.index') }}">
                    <img class="logo-img logo-img--footer" src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Moto Acessórios') }}">
                </a>
                <p>Sua loja virtual de peças, equipamentos e acessórios para motociclistas.</p>
                <div class="footer-social">
                    <a href="{{ config('app.contact.instagram_url', '#') }}" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>INSTITUCIONAL</h4>
                <ul>
                    <li><a href="{{ route('store.sobre') }}">Sobre nós</a></li>
                    <li><a href="{{ route('store.blog') }}">Blog</a></li>
                    <li><a href="{{ route('store.contato') }}">Fale Conosco</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>AJUDA</h4>
                <ul>
                    <li><a href="{{ route('store.trocas') }}">Trocas e devoluções</a></li>
                    <li><a href="{{ route('store.privacidade') }}">Política de privacidade</a></li>
                    <li><a href="{{ route('store.minha-conta') }}">Minha conta</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>ATENDIMENTO</h4>
                <ul class="footer-contact">
                    <li><i class="fa-brands fa-whatsapp"></i> +55 11 91593-3151</li>
                    <li><i class="fa-solid fa-phone"></i> +55 11 91593-3151</li>
                    <li><i class="fa-regular fa-envelope"></i> {{ config('app.contact.email', 'atendimento@motoacessorios.com.br') }}</li>
                    <li><i class="fa-regular fa-clock"></i> Seg a Sex: 08h as 18h30</li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>FORMAS DE PAGAMENTO</h4>
                <div class="payments">
                    <i class="fa-brands fa-cc-visa"></i>
                    <i class="fa-brands fa-cc-mastercard"></i>
                    <i class="fa-brands fa-cc-amex"></i>
                    <i class="fa-brands fa-pix"></i>
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <div class="footer-seals">
                    <span><i class="fa-solid fa-shield-halved"></i> Site protegido</span>
                    <span><i class="fa-solid fa-user-shield"></i> Dados criptografados</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-wrap">
                <p>© {{ now()->year }} Moto Acessórios. Todos os direitos reservados.</p>
                <p>CNPJ: 00.000.000/0001-00 | Curitiba - PR</p>
            </div>
        </div>
    </footer>

    <a class="floating-whats" href="{{ config('app.contact.whatsapp_url', '#') }}" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
    <button class="to-top" aria-label="Voltar ao topo"><i class="fa-solid fa-angle-up"></i></button>

    <script src="{{ asset('motoacessorios/script.js') }}"></script>
    @stack('pixel_events')
</body>
</html>
