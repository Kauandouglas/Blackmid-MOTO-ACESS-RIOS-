@extends('layouts.app')

@section('title', 'Política de Privacidade - ' . config('app.name'))

@section('content')
<section class="institutional-hero">
    <div class="container institutional-hero__inner">
        <div>
            <span class="eyebrow">Institucional</span>
            <h1>Política de privacidade</h1>
            <p>Entenda como a Moto Acessórios coleta, usa e protege seus dados durante navegação, compra, pagamento, entrega e atendimento.</p>
        </div>
        <aside class="institutional-summary">
            <div class="institutional-summary__item"><i class="fa-solid fa-user-shield"></i><span>Tratamento conforme LGPD</span></div>
            <div class="institutional-summary__item"><i class="fa-solid fa-lock"></i><span>Dados usados com finalidade</span></div>
            <div class="institutional-summary__item"><i class="fa-regular fa-envelope"></i><span>Canal para solicitações</span></div>
        </aside>
    </div>
</section>

<section class="institutional-section">
    <div class="container institutional-grid">
        <article class="institutional-card">
            <i class="fa-solid fa-cart-shopping"></i>
            <h2>Compra</h2>
            <p>Usamos dados para processar pedido, pagamento, emissão de documentos e entrega.</p>
        </article>
        <article class="institutional-card">
            <i class="fa-solid fa-headset"></i>
            <h2>Atendimento</h2>
            <p>Informações ajudam no suporte, acompanhamento, trocas e pós-venda.</p>
        </article>
        <article class="institutional-card">
            <i class="fa-solid fa-shield-halved"></i>
            <h2>Segurança</h2>
            <p>Dados técnicos ajudam a proteger a loja, prevenir fraude e melhorar a navegação.</p>
        </article>
    </div>
</section>

<section class="institutional-section">
    <div class="container institutional-layout">
        <aside class="institutional-panel institutional-panel--accent">
            <span class="eyebrow">Atualização</span>
            <h2>{{ now()->format('d/m/Y') }}</h2>
            <p>Para assuntos de privacidade, fale com nosso atendimento pelo e-mail cadastrado na loja.</p>
            <div class="institutional-actions">
                <a href="mailto:{{ config('app.contact.email') }}" class="btn btn--green"><i class="fa-regular fa-envelope"></i> Enviar e-mail</a>
            </div>
        </aside>

        <div class="institutional-panel">
            <h2>Dados que coletamos</h2>
            <div class="institutional-small-grid">
                <div class="institutional-mini">
                    <strong>Identificação</strong>
                    <p>Nome, e-mail, telefone, CPF/CNPJ e endereço.</p>
                </div>
                <div class="institutional-mini">
                    <strong>Pedido</strong>
                    <p>Produtos, histórico, pagamento, entrega e status.</p>
                </div>
                <div class="institutional-mini">
                    <strong>Navegação</strong>
                    <p>IP, dispositivo, navegador, páginas acessadas e cookies.</p>
                </div>
            </div>

            <div class="institutional-note"></div>

            <h2>Como usamos seus dados</h2>
            <ul class="institutional-list">
                <li><i class="fa-solid fa-check"></i><span>Processar pedidos, pagamentos, emissão de documentos e entregas.</span></li>
                <li><i class="fa-solid fa-check"></i><span>Prestar atendimento, suporte de pós-venda, trocas e acompanhamento de pedidos.</span></li>
                <li><i class="fa-solid fa-check"></i><span>Melhorar segurança, navegação, funcionamento da loja e prevenção de fraude.</span></li>
                <li><i class="fa-solid fa-check"></i><span>Enviar comunicações sobre pedidos e, quando autorizado, ofertas e novidades.</span></li>
            </ul>

            <div class="institutional-note"></div>

            <h2>Compartilhamento, retenção e direitos</h2>
            <p>Compartilhamos dados apenas com serviços necessários para operar a loja, como meios de pagamento, transportadoras, ferramentas antifraude, hospedagem e sistemas de atendimento. Você pode solicitar confirmação de tratamento, acesso, correção, portabilidade, anonimização, exclusão ou revisão de consentimento.</p>
        </div>
    </div>
</section>
@endsection
