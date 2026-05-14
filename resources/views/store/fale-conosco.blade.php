@extends('layouts.app')

@section('title', 'Fale Conosco - ' . config('app.name'))

@section('content')
<section class="contact-hero">
    <div class="wrap contact-hero__inner">
        <div>
            <span class="eyebrow">Atendimento</span>
            <h1>Fale Conosco</h1>
            <p>Precisa de ajuda para escolher uma peca, rastrear pedido ou falar sobre pagamento? Nosso time te atende com agilidade.</p>
        </div>
        <a href="{{ config('app.contact.whatsapp_url', '#') }}" target="_blank" rel="noopener noreferrer" class="btn btn--yellow">
            <i class="fa-brands fa-whatsapp"></i> CHAMAR NO WHATSAPP
        </a>
    </div>
</section>

<section class="support-section">
    <div class="wrap support-grid">
        <article class="support-card support-card--primary">
            <i class="fa-brands fa-whatsapp"></i>
            <h2>WhatsApp</h2>
            <p>Para pedidos, duvidas rapidas, rastreio e disponibilidade de produtos.</p>
            <a href="{{ config('app.contact.whatsapp_url', '#') }}" target="_blank" rel="noopener noreferrer">Iniciar conversa</a>
        </article>

        <article class="support-card">
            <i class="fa-regular fa-envelope"></i>
            <h2>E-mail</h2>
            <p>Envie sua solicitacao com numero do pedido para agilizar o atendimento.</p>
            <a href="mailto:{{ config('app.contact.email', 'atendimento@motoacessorios.com.br') }}">{{ config('app.contact.email', 'atendimento@motoacessorios.com.br') }}</a>
        </article>

        <article class="support-card">
            <i class="fa-solid fa-clock"></i>
            <h2>Horario</h2>
            <p>Segunda a sexta das 08h as 18h30. Respondemos por ordem de chegada.</p>
            <span>Atendimento especializado</span>
        </article>
    </div>
</section>

<section class="contact-info">
    <div class="wrap contact-info__grid">
        <div class="contact-panel">
            <h2>Suporte rapido</h2>
            <ul>
                <li><i class="fa-solid fa-box"></i> Duvidas sobre pedidos e entregas</li>
                <li><i class="fa-solid fa-rotate-right"></i> Trocas e devolucoes</li>
                <li><i class="fa-solid fa-credit-card"></i> Pagamento via Mercado Pago</li>
                <li><i class="fa-solid fa-motorcycle"></i> Compatibilidade de pecas e acessorios</li>
            </ul>
        </div>
        <div class="contact-panel contact-panel--dark">
            <h2>Antes de chamar</h2>
            <p>Tenha em maos o numero do pedido, modelo da moto ou link do produto. Assim a gente resolve mais rapido.</p>
            <a href="{{ route('store.search') }}" class="btn btn--green">BUSCAR PRODUTOS</a>
        </div>
    </div>
</section>
@endsection
