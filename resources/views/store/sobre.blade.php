@extends('layouts.app')

@section('title', 'Sobre nós - ' . config('app.name'))

@section('content')
<section class="bg-[#f9f6f3] border-b border-gray-200 py-14 text-center">
    <p class="text-[11px] tracking-[.25em] text-muted uppercase mb-3 font-medium">Quem somos</p>
    <h1 class="title-elegant text-ink leading-tight" style="font-size:clamp(2rem,4.5vw,4rem)">Moto Acessórios</h1>
    <p class="text-muted mt-4 max-w-2xl mx-auto px-4">Peças, equipamentos e acessórios para quem vive a rotina sobre duas rodas.</p>
</section>

<section class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
    <div>
        <span class="inline-block text-[11px] tracking-[.22em] text-muted uppercase mb-6 border-b border-gray-300 pb-2">Nossa loja</span>
        <h2 class="title-elegant text-ink mb-7" style="font-size:clamp(1.8rem,3.2vw,3rem)">Tudo para equipar sua moto com confiança</h2>
        <p class="text-muted leading-relaxed text-[15px] mb-5">
            A Moto Acessórios nasceu para facilitar a compra de produtos para motociclistas, reunindo peças, capacetes, vestuário, acessórios e itens de manutenção em uma experiência simples e segura.
        </p>
        <p class="text-muted leading-relaxed text-[15px] mb-5">
            Nosso foco é ajudar você a encontrar o produto certo para sua moto e para seu estilo de pilotagem, com informações claras, atendimento próximo e envio para todo o Brasil.
        </p>
        <p class="text-muted leading-relaxed text-[15px] mb-5">
            Trabalhamos com uma curadoria voltada para qualidade, compatibilidade e custo-benefício, sempre pensando na rotina real de quem usa a moto para trabalho, viagem, lazer ou deslocamento diário.
        </p>
        <p class="title-elegant text-ink text-xl mt-8">
            Moto Acessórios.<br>
            <span class="text-muted text-base font-normal tracking-wide">Sua moto pronta para o próximo caminho.</span>
        </p>
    </div>
    <div class="relative">
        <img src="{{ asset('about.jpg') }}" alt="Moto e acessórios" class="w-full object-contain rounded-sm shadow-lg">
    </div>
</section>

<section class="bg-[#f9f6f3] py-16">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10">
        <div class="text-center mb-12">
            <span class="inline-block text-[11px] tracking-[.22em] text-muted uppercase mb-4">O que nos guia</span>
            <h2 class="title-elegant text-ink" style="font-size:clamp(1.6rem,2.8vw,2.6rem)">Nosso compromisso</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-8 shadow-sm text-center">
                <i class="fa-solid fa-shield-halved text-3xl text-ink mb-5"></i>
                <h3 class="font-semibold text-ink text-base tracking-wide mb-3">Compra segura</h3>
                <p class="text-muted text-sm leading-relaxed">Pagamento protegido, dados tratados com cuidado e acompanhamento do pedido do início ao pós-venda.</p>
            </div>

            <div class="bg-white p-8 shadow-sm text-center">
                <i class="fa-solid fa-screwdriver-wrench text-3xl text-ink mb-5"></i>
                <h3 class="font-semibold text-ink text-base tracking-wide mb-3">Produto certo</h3>
                <p class="text-muted text-sm leading-relaxed">Atendimento para tirar dúvidas de compatibilidade, medidas, aplicação e escolha de acessórios.</p>
            </div>

            <div class="bg-white p-8 shadow-sm text-center sm:col-span-2 lg:col-span-1">
                <i class="fa-solid fa-truck-fast text-3xl text-ink mb-5"></i>
                <h3 class="font-semibold text-ink text-base tracking-wide mb-3">Entrega nacional</h3>
                <p class="text-muted text-sm leading-relaxed">Envio para todo o Brasil com cálculo de frete no checkout e suporte para acompanhar sua entrega.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 text-center max-w-[1200px] mx-auto px-6 lg:px-10">
    <h2 class="title-elegant text-ink mb-5" style="font-size:clamp(1.6rem,2.8vw,2.6rem)">Encontre o que sua moto precisa</h2>
    <p class="text-muted text-[15px] mb-10 max-w-xl mx-auto leading-relaxed">Veja categorias, compare produtos e chame nosso atendimento quando precisar de ajuda para escolher.</p>
    <a href="{{ route('store.index') }}" class="inline-block bg-ink text-white text-[13px] tracking-[.18em] uppercase px-10 py-4 hover:bg-opacity-85 transition font-medium">Ver produtos</a>
</section>
@endsection
