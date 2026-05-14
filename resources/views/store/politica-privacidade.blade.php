@extends('layouts.app')

@section('title', 'Política de Privacidade - ' . config('app.name'))

@section('content')
<section class="bg-[#f9f6f3] border-b border-gray-200 py-12">
    <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
        <p class="text-[11px] tracking-[.22em] text-muted uppercase mb-3">Institucional</p>
        <h1 class="title-elegant text-ink" style="font-size:clamp(1.8rem,4vw,3rem)">Política de Privacidade</h1>
        <p class="text-sm text-muted mt-4">Última atualização: {{ now()->format('d/m/Y') }}</p>
    </div>
</section>

<section class="max-w-[1200px] mx-auto px-4 lg:px-8 py-12 space-y-8 text-muted leading-relaxed">
    <p>Na Moto Acessórios, tratamos seus dados pessoais com seriedade e transparência. Esta política explica como coletamos, usamos e protegemos suas informações durante a navegação, compra e atendimento, conforme a LGPD.</p>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">1. Dados que coletamos</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Dados de identificação e contato: nome, e-mail, telefone, CPF/CNPJ e endereço.</li>
            <li>Dados do pedido: produtos comprados, histórico de compras, pagamento, entrega e status do pedido.</li>
            <li>Dados técnicos: endereço IP, navegador, dispositivo, páginas acessadas e cookies necessários ao funcionamento do site.</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">2. Como usamos seus dados</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Processar pedidos, pagamentos, emissão de documentos e entregas.</li>
            <li>Prestar atendimento, suporte de pós-venda, trocas e acompanhamento de pedidos.</li>
            <li>Melhorar a segurança, navegação e funcionamento da loja.</li>
            <li>Enviar comunicações sobre pedidos e, quando autorizado, ofertas e novidades.</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">3. Compartilhamento</h2>
        <p>Compartilhamos dados apenas com serviços necessários para operar a loja, como meios de pagamento, transportadoras, ferramentas antifraude, hospedagem e sistemas de atendimento. Não vendemos seus dados pessoais.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">4. Segurança e retenção</h2>
        <p>Guardamos as informações pelo tempo necessário para cumprir finalidades comerciais, fiscais, legais e de segurança. Adotamos medidas para reduzir riscos de acesso indevido, perda ou uso não autorizado.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">5. Seus direitos</h2>
        <p>Você pode solicitar confirmação de tratamento, acesso, correção, portabilidade, anonimização, exclusão ou revisão de consentimento, conforme a legislação aplicável.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">6. Cookies</h2>
        <p>Usamos cookies para manter o carrinho, autenticação, segurança, estatísticas e melhoria da experiência. Você pode ajustar permissões de cookies no seu navegador.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">7. Contato</h2>
        <p>Para assuntos de privacidade, fale com nosso atendimento: <a href="mailto:{{ config('app.contact.email') }}" class="underline hover:text-ink">{{ config('app.contact.email') }}</a>.</p>
    </div>
</section>
@endsection
