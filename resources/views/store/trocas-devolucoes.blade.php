@extends('layouts.app')

@section('title', 'Trocas e Devoluções - ' . config('app.name'))

@section('content')
<section class="contact-hero">
    <div class="container contact-hero__inner">
        <div>
            <span class="eyebrow">Ajuda</span>
            <h1>Trocas e devoluções</h1>
            <p>Pedido com problema? Veja o que precisa conferir e fale com a gente pelo canal certo para resolver sem enrolação.</p>
        </div>
        <a href="{{ config('app.contact.whatsapp_url', '#') }}" target="_blank" rel="noopener noreferrer" class="btn btn--yellow">
            <i class="fa-brands fa-whatsapp"></i> CHAMAR NO WHATSAPP
        </a>
    </div>
</section>

<section class="support-section">
    <div class="container support-grid">
        <article class="support-card">
            <i class="fa-regular fa-calendar-check"></i>
            <h2>Prazo</h2>
            <p>Solicite troca ou devolução em até 7 dias corridos após receber o pedido.</p>
            <span>Conte a partir da entrega</span>
        </article>

        <article class="support-card">
            <i class="fa-solid fa-box-open"></i>
            <h2>Produto</h2>
            <p>O item precisa estar completo, sem uso, sem instalação, com embalagem, etiquetas e acessórios.</p>
            <span>Preserve tudo que recebeu</span>
        </article>

        <article class="support-card">
            <i class="fa-solid fa-camera"></i>
            <h2>Análise</h2>
            <p>Para avaria, defeito ou item incorreto, envie fotos do produto e da embalagem.</p>
            <span>Fotos aceleram o suporte</span>
        </article>
    </div>
</section>

<section class="contact-info">
    <div class="container contact-info__grid">
        <div class="contact-panel">
            <h2>Condições da troca</h2>
            <ul>
                <li><i class="fa-solid fa-check"></i> Produto sem sinais de uso, queda, risco, odor, lavagem ou alteração.</li>
                <li><i class="fa-solid fa-check"></i> Capacetes e vestuário devem retornar limpos e com etiquetas preservadas.</li>
                <li><i class="fa-solid fa-check"></i> Peças elétricas, eletrônicas ou instaladas dependem de análise técnica.</li>
                <li><i class="fa-solid fa-check"></i> Produtos incompletos ou fora das condições podem ser recusados.</li>
            </ul>
        </div>

        <div class="contact-panel contact-panel--dark">
            <h2>Como solicitar</h2>
            <p>Envie número do pedido, nome completo, motivo da solicitação e fotos do produto. Se houver erro de envio ou defeito confirmado, orientamos o frete conforme análise.</p>
            <a href="mailto:{{ config('app.contact.email') }}" class="btn btn--green">
                <i class="fa-regular fa-envelope"></i> ENVIAR E-MAIL
            </a>
        </div>
    </div>
</section>
@endsection
