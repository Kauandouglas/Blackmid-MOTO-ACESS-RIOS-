@extends('layouts.app')

@section('title', 'Sobre nós - ' . config('app.name'))

@section('content')
<section class="institutional-hero">
    <div class="container institutional-hero__inner">
        <div>
            <span class="eyebrow">Sobre a loja</span>
            <h1>Moto Acessórios</h1>
            <p>Peças, equipamentos e acessórios para quem vive a rotina sobre duas rodas. Sem foto genérica, sem enrolação: informação clara, atendimento próximo e compra segura.</p>
        </div>
        <aside class="institutional-summary">
            <div class="institutional-summary__item"><i class="fa-solid fa-motorcycle"></i><span>Produtos para motociclistas</span></div>
            <div class="institutional-summary__item"><i class="fa-solid fa-truck-fast"></i><span>Envio para todo Brasil</span></div>
            <div class="institutional-summary__item"><i class="fa-solid fa-headset"></i><span>Atendimento especializado</span></div>
        </aside>
    </div>
</section>

<section class="institutional-section">
    <div class="container institutional-grid">
        <article class="institutional-card">
            <i class="fa-solid fa-helmet-safety"></i>
            <h2>Proteção</h2>
            <p>Capacetes, vestuário e itens para pilotar com mais segurança e conforto no dia a dia.</p>
        </article>
        <article class="institutional-card">
            <i class="fa-solid fa-gears"></i>
            <h2>Peças</h2>
            <p>Produtos para reposição, manutenção, elétrica e cuidados com sua moto.</p>
        </article>
        <article class="institutional-card">
            <i class="fa-solid fa-box-open"></i>
            <h2>Acessórios</h2>
            <p>Itens para equipar, organizar e melhorar a experiência de quem usa moto todo dia.</p>
        </article>
    </div>
</section>

<section class="institutional-section">
    <div class="container institutional-layout">
        <aside class="institutional-panel institutional-panel--accent">
            <span class="eyebrow">Nosso foco</span>
            <h2>Comprar certo</h2>
            <p>Se tiver dúvida de compatibilidade, medida, aplicação ou modelo, o atendimento ajuda antes de você fechar o pedido.</p>
            <div class="institutional-actions">
                <a href="{{ route('store.index') }}" class="btn btn--green"><i class="fa-solid fa-store"></i> Ver produtos</a>
                <a href="{{ route('store.contato') }}" class="btn btn-outline"><i class="fa-brands fa-whatsapp"></i> Atendimento</a>
            </div>
        </aside>

        <div class="institutional-panel">
            <h2>Tudo para equipar sua moto com confiança</h2>
            <p>A Moto Acessórios nasceu para facilitar a compra de produtos para motociclistas, reunindo peças, capacetes, vestuário, acessórios e itens de manutenção em uma experiência simples e segura.</p>

            <div class="institutional-note"></div>

            <div class="institutional-grid institutional-grid--two">
                <article>
                    <h3>Informação clara</h3>
                    <p>Descrições, fotos e atendimento para entender aplicação, tamanho, modelo e cuidados do produto.</p>
                </article>
                <article>
                    <h3>Pedido acompanhado</h3>
                    <p>Suporte para pagamento, entrega, troca, dúvidas de produto e orientações de pós-venda.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
