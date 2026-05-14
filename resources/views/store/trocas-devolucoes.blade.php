@extends('layouts.app')

@section('title', 'Política de Trocas e Devoluções - ' . config('app.name'))

@section('content')
<section class="contact-hero">
    <div class="container contact-hero__inner">
        <div>
            <span class="eyebrow">Ajuda</span>
            <h1>Trocas e devoluções</h1>
            <p>Na Moto Acessórios, prezamos pela satisfação dos nossos clientes e seguimos todas as normas do Código de Defesa do Consumidor.</p>
        </div>
        <a href="{{ config('app.contact.whatsapp_url', '#') }}" target="_blank" rel="noopener noreferrer" class="btn btn--yellow">
            <i class="fa-brands fa-whatsapp"></i> CHAMAR NO WHATSAPP
        </a>
    </div>
</section>

<section class="support-section">
    <div class="container support-grid">
        <article class="support-card">
            <i class="fa-solid fa-rotate-right"></i>
            <h2>Trocas e Devoluções</h2>
            <p>Caso seja necessário realizar uma troca ou devolução, o cliente poderá solicitar dentro dos prazos informados nesta política.</p>
            <span>Atendimento por pedido</span>
        </article>

        <article class="support-card">
            <i class="fa-regular fa-calendar-check"></i>
            <h2>Arrependimento</h2>
            <p>Compras realizadas online poderão ser canceladas em até 7 dias corridos após o recebimento do produto.</p>
            <span>Artigo 49 do CDC</span>
        </article>

        <article class="support-card">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <h2>Produto com Defeito</h2>
            <p>Produtos com defeito de fabricação poderão ser analisados pela nossa equipe após o recebimento.</p>
            <span>Análise após retorno</span>
        </article>
    </div>
</section>

<section class="contact-info">
    <div class="container contact-info__grid">
        <div class="contact-panel">
            <h2>Direito de Arrependimento</h2>
            <p>Conforme o artigo 49 do Código de Defesa do Consumidor, compras realizadas online poderão ser canceladas em até 7 (sete) dias corridos após o recebimento do produto.</p>
            <p>Para que a devolução seja aprovada, o produto deverá:</p>
            <ul>
                <li><i class="fa-solid fa-check"></i> Estar sem sinais de uso;</li>
                <li><i class="fa-solid fa-check"></i> Conter embalagem original;</li>
                <li><i class="fa-solid fa-check"></i> Estar acompanhado de etiquetas e acessórios;</li>
                <li><i class="fa-solid fa-check"></i> Possuir nota fiscal ou comprovante da compra.</li>
            </ul>
        </div>

        <div class="contact-panel contact-panel--dark">
            <h2>Como Solicitar</h2>
            <p>Para solicitar uma troca ou devolução, entre em contato através dos nossos canais de atendimento informando:</p>
            <ul>
                <li><i class="fa-solid fa-hashtag"></i> Número do pedido;</li>
                <li><i class="fa-solid fa-user"></i> Nome completo;</li>
                <li><i class="fa-solid fa-message"></i> Motivo da solicitação;</li>
                <li><i class="fa-solid fa-camera"></i> Fotos do produto, se necessário.</li>
            </ul>
            <p>Nossa equipe retornará com todas as orientações para envio.</p>
            <a href="mailto:{{ config('app.contact.email') }}" class="btn btn--green">
                <i class="fa-regular fa-envelope"></i> ENVIAR E-MAIL
            </a>
        </div>
    </div>
</section>

<section class="contact-info">
    <div class="container contact-info__grid">
        <div class="contact-panel">
            <h2>Produto com Defeito</h2>
            <p>Confirmado o defeito de fabricação após análise da nossa equipe, o cliente poderá optar por:</p>
            <ul>
                <li><i class="fa-solid fa-check"></i> Troca do produto;</li>
                <li><i class="fa-solid fa-check"></i> Crédito na loja;</li>
                <li><i class="fa-solid fa-check"></i> Reembolso do valor pago.</li>
            </ul>
        </div>

        <div class="contact-panel">
            <h2>Reembolso</h2>
            <p>O reembolso será realizado conforme a forma de pagamento utilizada na compra:</p>
            <ul>
                <li><i class="fa-solid fa-credit-card"></i> Cartão de crédito: estorno realizado pela operadora;</li>
                <li><i class="fa-brands fa-pix"></i> PIX ou boleto: transferência para conta bancária do titular da compra.</li>
            </ul>
        </div>
    </div>
</section>

<section class="contact-info">
    <div class="container">
        <div class="contact-panel contact-panel--dark">
            <h2>Importante</h2>
            <p>Não realizamos trocas ou devoluções de produtos com indícios de mau uso, danos causados pelo cliente ou fora dos prazos informados acima.</p>
        </div>
    </div>
</section>
@endsection
