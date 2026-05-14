@extends('layouts.app')

@section('title', 'Política de Troca — ' . config('app.name'))

@section('content')
<section class="bg-[#f9f6f3] border-b border-gray-200 py-12">
    <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
        <p class="text-[11px] tracking-[.22em] text-muted uppercase mb-3">Institucional</p>
        <h1 class="title-elegant text-ink" style="font-size:clamp(1.8rem,4vw,3rem)">Política de Troca</h1>
        <p class="text-sm text-muted mt-4">Última atualização: {{ now()->format('d/m/Y') }}</p>
    </div>
</section>

<section class="max-w-[1200px] mx-auto px-4 lg:px-8 py-12 space-y-8 text-muted leading-relaxed">
    <p>Confira abaixo as regras da nossa política de troca. No momento, não realizamos devolução de dinheiro. A troca é feita somente por outro produto disponível na loja e segue as condições descritas nesta página.</p>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">1. Condições gerais para troca</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Realizamos trocas apenas de peças em ótimas condições, sem sinais de uso, manchas ou odores, incluindo perfume, desodorante ou qualquer outro tipo de fragrância.</li>
            <li>Todas as despesas de envio ou transporte da troca são de responsabilidade do cliente.</li>
            <li>A troca é feita somente por outro produto disponível na loja.</li>
            <li>É permitida apenas uma troca por compra.</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">2. Prazo para solicitar a troca</h2>
        <p>A solicitação de troca deve ser feita em até 7 dias após o recebimento da peça. Após a solicitação dentro desse prazo, a troca pode ser realizada em até 30 dias após o recebimento.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">3. O que não trocamos</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Peças brancas não têm troca.</li>
            <li>Produtos em promoção não têm troca.</li>
            <li>Peças com sinais de uso, manchas, lavagem, ajustes ou odores não serão aceitas.</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">4. Forma da troca</h2>
        <p>Não realizamos devolução de dinheiro. Quando a troca for aprovada, a cliente poderá trocar apenas por outro produto disponível na loja no momento do atendimento.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">5. Custos de envio</h2>
        <p>Todas as despesas de envio, postagem ou transporte relacionadas à troca são de responsabilidade da cliente.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">6. Como solicitar</h2>
        <p>Envie e-mail para <a href="mailto:{{ config('app.contact.email') }}" class="underline hover:text-ink">{{ config('app.contact.email') }}</a> com número do pedido, nome completo e motivo da solicitação. Nossa equipe vai orientar os próximos passos da troca.</p>
    </div>
</section>
@endsection
