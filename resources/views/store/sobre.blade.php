@extends('layouts.app')

@section('title', 'Sobre Nós — ' . config('app.name'))

@section('content')

    {{-- ===== HEADER ===== --}}
    <section class="bg-[#f9f6f3] border-b border-gray-200 py-14 text-center">
        <p class="text-[11px] tracking-[.25em] text-muted uppercase mb-3 font-medium">Quem somos</p>
        <h1 class="title-elegant text-ink leading-tight" style="font-size:clamp(2rem,4.5vw,4rem)">Origem Brasileira</h1>
    </section>

    {{-- ===== INTRO ===== --}}
    <section class="max-w-[1600px] mx-auto px-6 lg:px-10 py-20 grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
        <div>
            <span class="inline-block text-[11px] tracking-[.22em] text-muted uppercase mb-6 border-b border-gray-300 pb-2">Nossa História</span>
            <h2 class="title-elegant text-ink mb-7" style="font-size:clamp(1.8rem,3.2vw,3rem)">Nascemos do<br>desejo de pertencer</h2>
            <p class="text-muted leading-relaxed text-[15px] mb-5">
                A Origem Brasileira nasceu do desejo de levar um pedacinho do Brasil para mulheres que vivem na Inglaterra — mulheres que valorizam sua essência, sua cultura e seu estilo único.
            </p>
            <p class="text-muted leading-relaxed text-[15px] mb-5">
                Somos uma loja pensada especialmente para clientes no Brasil que buscam produtos com estilo, qualidade e praticidade. Acreditamos que cada compra deve ser simples, segura e bem acompanhada do carrinho ate a entrega.
            </p>
            <p class="text-muted leading-relaxed text-[15px] mb-5">
                Nossa curadoria é feita com carinho, escolhendo peças que unem conforto, elegância e aquele toque especial da moda brasileira — leve, versátil e cheia de personalidade. Trabalhamos para oferecer roupas que acompanham a rotina da mulher moderna, desde os momentos mais simples até as ocasiões especiais.
            </p>
            <p class="text-muted leading-relaxed text-[15px] mb-5">
                Mais do que uma loja, somos uma ponte entre culturas. Levamos até você a beleza, a alegria e o estilo do Brasil, com praticidade e entrega para toda a Inglaterra.
            </p>
            <p class="text-muted leading-relaxed text-[15px] mb-5">
                Aqui, cada cliente é única. Nosso compromisso é oferecer não apenas produtos de qualidade, mas também uma experiência acolhedora, próxima e cheia de cuidado — como se você estivesse comprando no Brasil.
            </p>
            <p class="title-elegant text-ink text-xl mt-8">
                Seja bem-vinda à Origem Brasileira.<br>
                <span class="text-muted text-base font-normal tracking-wide">Vista sua essência.</span>
            </p>
        </div>
        <div class="relative">
            <img src="{{ asset('about.jpg') }}" alt="Nossa equipe" class="w-full object-contain rounded-sm shadow-lg">
        </div>
    </section>

    {{-- ===== VALORES ===== --}}
    <section class="bg-[#f9f6f3] py-20">
        <div class="max-w-[1600px] mx-auto px-6 lg:px-10">
            <div class="text-center mb-14">
                <span class="inline-block text-[11px] tracking-[.22em] text-muted uppercase mb-4">O que nos move</span>
                <h2 class="title-elegant text-ink" style="font-size:clamp(1.6rem,2.8vw,2.6rem)">Nossos Valores</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">

                <div class="bg-white p-8 shadow-sm text-center">
                    <div class="w-12 h-12 mx-auto mb-5 flex items-center justify-center rounded-full bg-[#f0e9e2]">
                        <svg class="w-6 h-6 text-ink" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.714-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-ink text-base tracking-wide mb-3">Identidade</h3>
                    <p class="text-muted text-sm leading-relaxed">Celebramos a essência de cada mulher brasileira. Nossa moda é feita para quem não quer abrir mão de ser quem é.</p>
                </div>

                <div class="bg-white p-8 shadow-sm text-center">
                    <div class="w-12 h-12 mx-auto mb-5 flex items-center justify-center rounded-full bg-[#f0e9e2]">
                        <svg class="w-6 h-6 text-ink" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-ink text-base tracking-wide mb-3">Conexão</h3>
                    <p class="text-muted text-sm leading-relaxed">Mesmo longe de casa, queremos que você se sinta conectada às suas raízes, à sua cultura e à sua comunidade.</p>
                </div>

                <div class="bg-white p-8 shadow-sm text-center sm:col-span-2 lg:col-span-1">
                    <div class="w-12 h-12 mx-auto mb-5 flex items-center justify-center rounded-full bg-[#f0e9e2]">
                        <svg class="w-6 h-6 text-ink" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-ink text-base tracking-wide mb-3">Confiança</h3>
                    <p class="text-muted text-sm leading-relaxed">Roupas que fazem você se sentir linda e confiante todos os dias, em qualquer ocasião e em qualquer lugar do mundo.</p>
                </div>

            </div>
        </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section class="py-20 text-center max-w-[1600px] mx-auto px-6 lg:px-10">
        <h2 class="title-elegant text-ink mb-5" style="font-size:clamp(1.6rem,2.8vw,2.6rem)">Pronta para descobrir<br>sua próxima peça favorita?</h2>
        <p class="text-muted text-[15px] mb-10 max-w-xl mx-auto leading-relaxed">Explore nossa colecao cuidadosamente selecionada para quem compra no Brasil com estilo e confianca.</p>
        <a href="{{ route('store.index') }}" class="inline-block bg-ink text-white text-[13px] tracking-[.18em] uppercase px-10 py-4 hover:bg-opacity-85 transition font-medium">Ver Coleção</a>
    </section>

@endsection
