@extends('layouts.app')

@section('title', 'Política de Privacidade — ' . config('app.name'))

@section('content')
<section class="bg-[#f9f6f3] border-b border-gray-200 py-12">
    <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
        <p class="text-[11px] tracking-[.22em] text-muted uppercase mb-3">Institucional</p>
        <h1 class="title-elegant text-ink" style="font-size:clamp(1.8rem,4vw,3rem)">Política de Privacidade</h1>
        <p class="text-sm text-muted mt-4">Última atualização: {{ now()->format('d/m/Y') }}</p>
    </div>
</section>

<section class="max-w-[1200px] mx-auto px-4 lg:px-8 py-12 space-y-8 text-muted leading-relaxed">
    <p>Na Origem Brasileira, tratamos seus dados pessoais com seriedade e transparência. Esta política explica como coletamos, usamos e protegemos suas informações, em conformidade com o UK GDPR e o Data Protection Act 2018.</p>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">1. Dados que coletamos</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Dados de identificação: nome, e-mail, telefone e endereço de entrega/faturamento.</li>
            <li>Dados de pedido: produtos comprados, histórico de compras e status do pedido.</li>
            <li>Dados técnicos: endereço IP, navegador, dispositivo e cookies de navegação.</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">2. Como usamos seus dados</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li>Processar pagamentos e entregas.</li>
            <li>Prestar suporte ao cliente e responder solicitações.</li>
            <li>Enviar atualizações sobre pedidos.</li>
            <li>Melhorar a experiência do site, segurança e prevenção de fraude.</li>
            <li>Enviar comunicações de marketing apenas quando houver consentimento.</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">3. Base legal</h2>
        <p>Tratamos dados com base em: execução de contrato, cumprimento de obrigação legal, interesse legítimo e consentimento (quando aplicável).</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">4. Compartilhamento de dados</h2>
        <p>Compartilhamos dados apenas com parceiros necessários para operar a loja, como processadores de pagamento, transportadoras e provedores de tecnologia. Não vendemos seus dados pessoais.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">5. Retenção e segurança</h2>
        <p>Armazenamos dados pelo tempo necessário para cumprir finalidades comerciais, fiscais e legais. Adotamos medidas técnicas e organizacionais para proteger informações contra acesso não autorizado.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">6. Seus direitos</h2>
        <p>Você pode solicitar acesso, correção, portabilidade, restrição, oposição ou exclusão dos seus dados, conforme a legislação aplicável no Brasil.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">7. Cookies</h2>
        <p>Usamos cookies para funcionamento do site, análise de uso e melhoria de experiência. Você pode gerenciar cookies nas configurações do seu navegador.</p>
    </div>

    <div>
        <h2 class="text-xl text-ink font-semibold mb-3">8. Contato</h2>
        <p>Para assuntos de privacidade, fale com nosso atendimento: <a href="mailto:{{ config('app.contact.email') }}" class="underline hover:text-ink">{{ config('app.contact.email') }}</a>.</p>
    </div>
</section>
@endsection
