@extends('layouts.app')

@section('title', 'Trocas e Devoluções - ' . config('app.name'))

@section('content')
<section class="bg-[#07100b] text-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-8 py-14 lg:py-18">
        <div class="max-w-3xl">
            <p class="text-[11px] uppercase tracking-[.24em] text-[#00d64f] mb-4">Ajuda</p>
            <h1 class="font-display text-4xl lg:text-6xl font-extrabold leading-none mb-5">Trocas e devoluções</h1>
            <p class="text-base lg:text-lg leading-relaxed text-slate-300">Veja os prazos, condições e o caminho certo para solicitar ajuda com peças, capacetes, vestuário e acessórios comprados na Moto Acessórios.</p>
        </div>
    </div>
</section>

<section class="max-w-[1280px] mx-auto px-4 lg:px-8 py-10">
    <div class="grid gap-4 md:grid-cols-3">
        <article class="border border-gray-200 bg-white p-6">
            <i class="fa-regular fa-calendar-check text-2xl text-[#00a83f] mb-4"></i>
            <h2 class="font-extrabold text-ink text-xl mb-2">7 dias corridos</h2>
            <p class="text-sm text-muted leading-relaxed">Solicite troca ou devolução em até 7 dias corridos após receber o pedido.</p>
        </article>
        <article class="border border-gray-200 bg-white p-6">
            <i class="fa-solid fa-box text-2xl text-[#00a83f] mb-4"></i>
            <h2 class="font-extrabold text-ink text-xl mb-2">Produto completo</h2>
            <p class="text-sm text-muted leading-relaxed">Envie com embalagem, etiquetas, manuais, acessórios e nota fiscal sempre que houver.</p>
        </article>
        <article class="border border-gray-200 bg-white p-6">
            <i class="fa-solid fa-camera text-2xl text-[#00a83f] mb-4"></i>
            <h2 class="font-extrabold text-ink text-xl mb-2">Fotos ajudam</h2>
            <p class="text-sm text-muted leading-relaxed">Para avaria, defeito ou item incorreto, envie fotos do produto e da embalagem.</p>
        </article>
    </div>
</section>

<section class="max-w-[1280px] mx-auto px-4 lg:px-8 pb-14 lg:pb-18">
    <div class="grid gap-8 lg:grid-cols-[320px_1fr]">
        <aside class="bg-[#f4f7f4] border border-gray-200 p-6 self-start">
            <p class="text-[11px] uppercase tracking-[.22em] text-muted mb-3">Como solicitar</p>
            <h2 class="text-2xl font-extrabold text-ink mb-4">Fale com o atendimento</h2>
            <p class="text-sm text-muted leading-relaxed mb-5">Tenha em mãos o número do pedido, nome completo, motivo da solicitação e fotos do produto.</p>
            <div class="grid gap-3">
                <a href="{{ config('app.contact.whatsapp_url', '#') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 bg-[#00d64f] text-[#031007] px-4 py-3 text-sm font-extrabold uppercase tracking-[.12em] hover:bg-[#f5c542] transition">
                    <i class="fa-brands fa-whatsapp"></i>
                    WhatsApp
                </a>
                <a href="mailto:{{ config('app.contact.email') }}" class="inline-flex items-center justify-center gap-2 border border-gray-300 bg-white px-4 py-3 text-sm font-extrabold uppercase tracking-[.12em] text-ink hover:border-[#00a83f] transition">
                    <i class="fa-regular fa-envelope"></i>
                    E-mail
                </a>
            </div>
        </aside>

        <div class="space-y-4">
            <article class="border border-gray-200 bg-white p-6">
                <div class="flex gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#07100b] text-white font-extrabold">1</span>
                    <div>
                        <h2 class="text-xl font-extrabold text-ink mb-2">Condições do produto</h2>
                        <ul class="grid gap-2 text-sm text-muted leading-relaxed">
                            <li>O item deve estar sem sinais de uso, instalação, lavagem, queda, risco, odor ou alteração.</li>
                            <li>Capacetes e vestuário devem retornar limpos, sem marcas de uso e com etiquetas preservadas.</li>
                            <li>Peças elétricas, eletrônicas ou instaladas dependem de análise técnica.</li>
                        </ul>
                    </div>
                </div>
            </article>

            <article class="border border-gray-200 bg-white p-6">
                <div class="flex gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#07100b] text-white font-extrabold">2</span>
                    <div>
                        <h2 class="text-xl font-extrabold text-ink mb-2">Produto incorreto, avaria ou defeito</h2>
                        <p class="text-sm text-muted leading-relaxed">Se recebeu um item diferente do comprado, com avaria de transporte ou possível defeito de fabricação, avise nosso atendimento assim que identificar o problema.</p>
                    </div>
                </div>
            </article>

            <article class="border border-gray-200 bg-white p-6">
                <div class="flex gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#07100b] text-white font-extrabold">3</span>
                    <div>
                        <h2 class="text-xl font-extrabold text-ink mb-2">Frete, crédito ou reembolso</h2>
                        <p class="text-sm text-muted leading-relaxed">Quando houver erro no envio ou defeito confirmado, orientamos o processo e tratamos o frete conforme análise. Em trocas por tamanho, modelo, cor ou arrependimento, os custos de envio podem ser de responsabilidade do cliente. Após aprovação, o valor pode voltar pelo mesmo meio de pagamento ou virar crédito para nova compra.</p>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
