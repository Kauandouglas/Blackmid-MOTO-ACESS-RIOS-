@extends('layouts.app')

@section('title', 'Política de Privacidade - ' . config('app.name'))

@section('content')
<section class="contact-hero">
    <div class="container contact-hero__inner">
        <div>
            <span class="eyebrow">Institucional</span>
            <h1>Política de privacidade</h1>
            <p>Resumo claro de como usamos seus dados para compra, pagamento, entrega, segurança e atendimento na Moto Acessórios.</p>
        </div>
        <a href="mailto:{{ config('app.contact.email') }}" class="btn btn--yellow">
            <i class="fa-regular fa-envelope"></i> FALE CONOSCO
        </a>
    </div>
</section>

<section class="support-section">
    <div class="container support-grid">
        <article class="support-card">
            <i class="fa-solid fa-user-shield"></i>
            <h2>LGPD</h2>
            <p>Tratamos dados pessoais com finalidade, transparência e cuidado.</p>
            <span>Privacidade levada a sério</span>
        </article>

        <article class="support-card">
            <i class="fa-solid fa-cart-shopping"></i>
            <h2>Compra</h2>
            <p>Usamos informações para pedido, pagamento, emissão de documentos e entrega.</p>
            <span>Dados para operar a loja</span>
        </article>

        <article class="support-card">
            <i class="fa-solid fa-lock"></i>
            <h2>Segurança</h2>
            <p>Dados técnicos ajudam a proteger o site, prevenir fraude e melhorar a navegação.</p>
            <span>Ambiente mais seguro</span>
        </article>
    </div>
</section>

<section class="contact-info">
    <div class="container contact-info__grid">
        <div class="contact-panel">
            <h2>Dados que podemos coletar</h2>
            <ul>
                <li><i class="fa-solid fa-user"></i> Nome, e-mail, telefone, CPF/CNPJ e endereço.</li>
                <li><i class="fa-solid fa-receipt"></i> Produtos comprados, histórico, pagamento, entrega e status do pedido.</li>
                <li><i class="fa-solid fa-laptop"></i> IP, dispositivo, navegador, páginas acessadas e cookies necessários.</li>
                <li><i class="fa-solid fa-headset"></i> Mensagens enviadas ao atendimento e informações de suporte.</li>
            </ul>
        </div>

        <div class="contact-panel contact-panel--dark">
            <h2>Seus direitos</h2>
            <p>Você pode solicitar acesso, correção, portabilidade, exclusão, anonimização ou revisão de consentimento. Compartilhamos dados apenas com serviços necessários para pagamento, transporte, antifraude, hospedagem e atendimento.</p>
            <a href="mailto:{{ config('app.contact.email') }}" class="btn btn--green">
                <i class="fa-regular fa-envelope"></i> SOLICITAR ATENDIMENTO
            </a>
        </div>
    </div>
</section>
@endsection
