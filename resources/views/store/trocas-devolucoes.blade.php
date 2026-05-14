@extends('layouts.app')

@section('title', 'Trocas e Devoluções - ' . config('app.name'))

@section('content')
<section class="bg-[#f9f6f3] border-b border-gray-200 py-12">
    <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
        <p class="text-[11px] tracking-[.22em] text-muted uppercase mb-3">Ajuda</p>
        <h1 class="title-elegant text-ink" style="font-size:clamp(1.8rem,4vw,3rem)">Trocas e Devoluções</h1>
        <p class="text-sm text-muted mt-4">Última atualização: {{ now()->format('d/m/Y') }}</p>
    </div>
</section>

<section class="max-w-[1200px] mx-auto px-4 lg:px-8 py-12 space-y-8 text-muted leading-relaxed">
    <p>Confira as condições para solicitar troca ou devolução de peças, capacetes, vestuário e acessórios comprados na Moto Acessórios.</p>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">1. Prazo para solicitar</h2>
        <p>Você pode solicitar troca ou devolução em até 7 dias corridos após o recebimento do pedido. Para produtos com defeito de fabricação, entre em contato informando o problema e imagens do item para análise.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">2. Condições do produto</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>O produto deve estar sem sinais de uso, instalação, lavagem, queda, risco, odor ou alteração.</li>
            <li>Embalagem, etiquetas, manuais, acessórios e nota fiscal devem acompanhar o item.</li>
            <li>Peças elétricas, eletrônicas ou instaladas dependem de análise técnica para aprovação da troca.</li>
            <li>Capacetes e vestuário devem retornar limpos, sem marcas de uso e com etiquetas preservadas.</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">3. Produto incorreto ou avariado</h2>
        <p>Se você recebeu um produto diferente do comprado ou com avaria de transporte, avise nosso atendimento assim que identificar o problema. Envie número do pedido, fotos da embalagem e fotos do produto.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">4. Frete de troca</h2>
        <p>Quando houver erro no envio ou defeito confirmado, orientamos o processo e assumimos o frete conforme análise. Em trocas por tamanho, modelo, cor ou arrependimento, os custos de envio podem ser de responsabilidade do cliente.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">5. Reembolso ou crédito</h2>
        <p>Após o recebimento e aprovação da análise, o valor pode ser devolvido pelo mesmo meio de pagamento ou convertido em crédito para uma nova compra, conforme combinado no atendimento.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">6. Como solicitar</h2>
        <p>Envie e-mail para <a href="mailto:{{ config('app.contact.email') }}" class="underline hover:text-ink">{{ config('app.contact.email') }}</a> ou chame no WhatsApp com número do pedido, nome completo, motivo da solicitação e fotos do produto.</p>
    </div>
</section>
@endsection
