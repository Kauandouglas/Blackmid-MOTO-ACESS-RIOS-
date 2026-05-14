@extends('layouts.app')

@section('title', 'Sobre nós - ' . config('app.name'))

@section('content')
<section class="bg-[#07100b] text-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-8 py-14 lg:py-20 grid gap-10 lg:grid-cols-[1.1fr_.9fr] items-center">
        <div>
            <p class="text-[11px] uppercase tracking-[.24em] text-[#00d64f] mb-4">Sobre a Moto Acessórios</p>
            <h1 class="font-display text-4xl lg:text-6xl font-extrabold leading-[.95] mb-5">Peças, equipamentos e suporte para quem roda de moto.</h1>
            <p class="text-base lg:text-lg leading-relaxed text-slate-300 max-w-2xl">
                Ajudamos motociclistas a comprarem com mais segurança, clareza e praticidade. Aqui você encontra capacetes, peças, elétrica, vestuário e acessórios com atendimento pronto para tirar dúvidas antes e depois da compra.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('store.index') }}" class="inline-flex items-center gap-2 bg-[#00d64f] text-[#031007] px-5 py-3 text-sm font-extrabold uppercase tracking-[.12em] hover:bg-white transition">
                    <i class="fa-solid fa-store"></i>
                    Ver produtos
                </a>
                <a href="{{ route('store.contato') }}" class="inline-flex items-center gap-2 border border-white/20 px-5 py-3 text-sm font-extrabold uppercase tracking-[.12em] text-white hover:border-[#00d64f] hover:text-[#00d64f] transition">
                    <i class="fa-brands fa-whatsapp"></i>
                    Atendimento
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="border border-white/10 bg-white/[.04] p-5">
                <i class="fa-solid fa-helmet-safety text-2xl text-[#00d64f] mb-4"></i>
                <p class="text-3xl font-extrabold">+5</p>
                <p class="text-sm text-slate-300">categorias para moto</p>
            </div>
            <div class="border border-white/10 bg-white/[.04] p-5">
                <i class="fa-solid fa-truck-fast text-2xl text-[#f5c542] mb-4"></i>
                <p class="text-3xl font-extrabold">BR</p>
                <p class="text-sm text-slate-300">envio para todo Brasil</p>
            </div>
            <div class="col-span-2 border border-white/10 bg-white/[.04] p-5">
                <i class="fa-solid fa-screwdriver-wrench text-2xl text-[#00d64f] mb-4"></i>
                <p class="text-xl font-extrabold mb-2">Compra orientada</p>
                <p class="text-sm leading-relaxed text-slate-300">Se tiver dúvida de compatibilidade, medida, aplicação ou modelo, nosso atendimento ajuda antes de você fechar o pedido.</p>
            </div>
        </div>
    </div>
</section>

<section class="max-w-[1280px] mx-auto px-4 lg:px-8 py-14 lg:py-18">
    <div class="grid gap-8 lg:grid-cols-[.8fr_1.2fr]">
        <div>
            <p class="text-[11px] uppercase tracking-[.22em] text-muted mb-3">Nossa forma de trabalhar</p>
            <h2 class="text-3xl lg:text-5xl font-extrabold text-ink leading-none">Direto ao ponto, sem complicar sua compra.</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <article class="border border-gray-200 bg-white p-6">
                <i class="fa-solid fa-circle-check text-2xl text-[#00a83f] mb-4"></i>
                <h3 class="font-extrabold text-ink mb-2">Informação clara</h3>
                <p class="text-sm text-muted leading-relaxed">Descrições, fotos e atendimento para você entender aplicação, tamanho, modelo e cuidados do produto.</p>
            </article>
            <article class="border border-gray-200 bg-white p-6">
                <i class="fa-solid fa-lock text-2xl text-[#00a83f] mb-4"></i>
                <h3 class="font-extrabold text-ink mb-2">Pagamento seguro</h3>
                <p class="text-sm text-muted leading-relaxed">Checkout protegido, opções de pagamento e acompanhamento do pedido até a conclusão da compra.</p>
            </article>
            <article class="border border-gray-200 bg-white p-6">
                <i class="fa-solid fa-box-open text-2xl text-[#00a83f] mb-4"></i>
                <h3 class="font-extrabold text-ink mb-2">Produtos para rotina real</h3>
                <p class="text-sm text-muted leading-relaxed">Itens para manutenção, reposição, proteção, pilotagem, trabalho, viagem e uso urbano.</p>
            </article>
            <article class="border border-gray-200 bg-white p-6">
                <i class="fa-solid fa-headset text-2xl text-[#00a83f] mb-4"></i>
                <h3 class="font-extrabold text-ink mb-2">Pós-venda próximo</h3>
                <p class="text-sm text-muted leading-relaxed">Ajuda com pedidos, entrega, trocas, dúvidas de produto e orientações para resolver rápido.</p>
            </article>
        </div>
    </div>
</section>

<section class="bg-[#f4f7f4] border-y border-gray-200">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-8 py-12 grid gap-5 md:grid-cols-3">
        <div>
            <p class="text-[11px] uppercase tracking-[.22em] text-muted mb-2">Categorias</p>
            <h2 class="text-2xl font-extrabold text-ink">O que você encontra aqui</h2>
        </div>
        <a href="{{ route('store.category', 'capacetes') }}" class="bg-white border border-gray-200 p-5 hover:border-[#00a83f] transition">
            <i class="fa-solid fa-helmet-safety text-xl text-[#00a83f] mb-3"></i>
            <h3 class="font-extrabold text-ink">Capacetes e proteção</h3>
            <p class="text-sm text-muted mt-1">Modelos para uso urbano, estrada e rotina diária.</p>
        </a>
        <a href="{{ route('store.category', 'pecas') }}" class="bg-white border border-gray-200 p-5 hover:border-[#00a83f] transition">
            <i class="fa-solid fa-gears text-xl text-[#00a83f] mb-3"></i>
            <h3 class="font-extrabold text-ink">Peças e manutenção</h3>
            <p class="text-sm text-muted mt-1">Reposição, elétrica, acessórios e itens para cuidar da moto.</p>
        </a>
    </div>
</section>
@endsection
