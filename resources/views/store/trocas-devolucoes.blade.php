@extends('layouts.app')

@section('title', 'Trocas e Devoluções - ' . config('app.name'))

@section('content')
<section class="institutional-hero">
    <div class="container institutional-hero__inner">
        <div>
            <span class="eyebrow">Ajuda</span>
            <h1>Trocas e devoluções</h1>
            <p>Confira os prazos, condições e o caminho certo para solicitar ajuda com peças, capacetes, vestuário e acessórios comprados na Moto Acessórios.</p>
        </div>
        <aside class="institutional-summary">
            <div class="institutional-summary__item"><i class="fa-regular fa-calendar-check"></i><span>Solicitação em até 7 dias corridos</span></div>
            <div class="institutional-summary__item"><i class="fa-solid fa-box"></i><span>Produto completo e sem uso</span></div>
            <div class="institutional-summary__item"><i class="fa-solid fa-camera"></i><span>Fotos agilizam a análise</span></div>
        </aside>
    </div>
</section>

<section class="institutional-section">
    <div class="container institutional-grid">
        <article class="institutional-card">
            <i class="fa-regular fa-calendar-check"></i>
            <h2>7 dias corridos</h2>
            <p>Você pode solicitar troca ou devolução em até 7 dias corridos após receber o pedido.</p>
        </article>
        <article class="institutional-card">
            <i class="fa-solid fa-box-open"></i>
            <h2>Produto completo</h2>
            <p>Embalagem, etiquetas, manuais, acessórios e nota fiscal devem acompanhar o item sempre que houver.</p>
        </article>
        <article class="institutional-card">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <h2>Análise técnica</h2>
            <p>Peças elétricas, eletrônicas ou instaladas passam por avaliação antes da aprovação.</p>
        </article>
    </div>
</section>

<section class="institutional-section">
    <div class="container institutional-layout">
        <aside class="institutional-panel institutional-panel--accent">
            <span class="eyebrow">Como solicitar</span>
            <h2>Fale com o atendimento</h2>
            <p>Tenha em mãos o número do pedido, nome completo, motivo da solicitação e fotos do produto.</p>
            <div class="institutional-actions">
                <a href="{{ config('app.contact.whatsapp_url', '#') }}" target="_blank" rel="noopener noreferrer" class="btn btn--green"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                <a href="mailto:{{ config('app.contact.email') }}" class="btn btn-outline"><i class="fa-regular fa-envelope"></i> E-mail</a>
            </div>
        </aside>

        <div class="institutional-panel">
            <div class="institutional-step">
                <span class="institutional-step__number">1</span>
                <div>
                    <h2>Condições do produto</h2>
                    <ul class="institutional-list">
                        <li><i class="fa-solid fa-check"></i><span>O item deve estar sem sinais de uso, instalação, lavagem, queda, risco, odor ou alteração.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Capacetes e vestuário devem retornar limpos, sem marcas de uso e com etiquetas preservadas.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Produtos enviados incompletos ou fora das condições podem ser recusados após análise.</span></li>
                    </ul>
                </div>
            </div>

            <div class="institutional-note"></div>

            <div class="institutional-step">
                <span class="institutional-step__number">2</span>
                <div>
                    <h2>Item incorreto, avaria ou defeito</h2>
                    <p>Se recebeu um item diferente do comprado, com avaria de transporte ou possível defeito de fabricação, avise nosso atendimento assim que identificar o problema. Envie fotos do produto e da embalagem.</p>
                </div>
            </div>

            <div class="institutional-note"></div>

            <div class="institutional-step">
                <span class="institutional-step__number">3</span>
                <div>
                    <h2>Frete, crédito ou reembolso</h2>
                    <p>Quando houver erro no envio ou defeito confirmado, orientamos o processo e tratamos o frete conforme análise. Em trocas por tamanho, modelo, cor ou arrependimento, os custos de envio podem ser de responsabilidade do cliente.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
