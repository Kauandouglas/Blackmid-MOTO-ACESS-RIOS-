@extends('layouts.app')

@section('title', 'Política de Privacidade - ' . config('app.name'))

@section('content')
<section class="bg-[#07100b] text-white">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-8 py-14 lg:py-18">
        <div class="max-w-3xl">
            <p class="text-[11px] uppercase tracking-[.24em] text-[#00d64f] mb-4">Institucional</p>
            <h1 class="font-display text-4xl lg:text-6xl font-extrabold leading-none mb-5">Política de privacidade</h1>
            <p class="text-base lg:text-lg leading-relaxed text-slate-300">Entenda como a Moto Acessórios coleta, usa e protege seus dados durante a navegação, compra, pagamento, entrega e atendimento.</p>
        </div>
    </div>
</section>

<section class="max-w-[1280px] mx-auto px-4 lg:px-8 py-10">
    <div class="grid gap-4 md:grid-cols-4">
        <article class="border border-gray-200 bg-white p-5">
            <i class="fa-solid fa-user-shield text-2xl text-[#00a83f] mb-4"></i>
            <h2 class="font-extrabold text-ink mb-2">LGPD</h2>
            <p class="text-sm text-muted leading-relaxed">Tratamento de dados com transparência e finalidade.</p>
        </article>
        <article class="border border-gray-200 bg-white p-5">
            <i class="fa-solid fa-cart-shopping text-2xl text-[#00a83f] mb-4"></i>
            <h2 class="font-extrabold text-ink mb-2">Compra</h2>
            <p class="text-sm text-muted leading-relaxed">Dados usados para pedido, pagamento e entrega.</p>
        </article>
        <article class="border border-gray-200 bg-white p-5">
            <i class="fa-solid fa-lock text-2xl text-[#00a83f] mb-4"></i>
            <h2 class="font-extrabold text-ink mb-2">Segurança</h2>
            <p class="text-sm text-muted leading-relaxed">Medidas para reduzir acesso indevido.</p>
        </article>
        <article class="border border-gray-200 bg-white p-5">
            <i class="fa-solid fa-headset text-2xl text-[#00a83f] mb-4"></i>
            <h2 class="font-extrabold text-ink mb-2">Contato</h2>
            <p class="text-sm text-muted leading-relaxed">Solicite atendimento sobre seus dados.</p>
        </article>
    </div>
</section>

<section class="max-w-[1280px] mx-auto px-4 lg:px-8 pb-14 lg:pb-18">
    <div class="grid gap-8 lg:grid-cols-[320px_1fr]">
        <aside class="bg-[#f4f7f4] border border-gray-200 p-6 self-start">
            <p class="text-[11px] uppercase tracking-[.22em] text-muted mb-3">Atualização</p>
            <h2 class="text-2xl font-extrabold text-ink mb-2">{{ now()->format('d/m/Y') }}</h2>
            <p class="text-sm text-muted leading-relaxed mb-5">Esta página resume as práticas de privacidade da loja. Para solicitações, fale com nosso atendimento.</p>
            <a href="mailto:{{ config('app.contact.email') }}" class="inline-flex items-center justify-center gap-2 w-full bg-[#07100b] text-white px-4 py-3 text-sm font-extrabold uppercase tracking-[.12em] hover:bg-[#00a83f] transition">
                <i class="fa-regular fa-envelope"></i>
                Enviar e-mail
            </a>
        </aside>

        <div class="space-y-4">
            <article class="border border-gray-200 bg-white p-6">
                <h2 class="text-xl font-extrabold text-ink mb-3">1. Dados que coletamos</h2>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="bg-gray-50 border border-gray-100 p-4">
                        <h3 class="font-extrabold text-ink mb-1">Identificação</h3>
                        <p class="text-sm text-muted leading-relaxed">Nome, e-mail, telefone, CPF/CNPJ e endereço.</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 p-4">
                        <h3 class="font-extrabold text-ink mb-1">Pedido</h3>
                        <p class="text-sm text-muted leading-relaxed">Produtos, histórico, pagamento, entrega e status.</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 p-4">
                        <h3 class="font-extrabold text-ink mb-1">Navegação</h3>
                        <p class="text-sm text-muted leading-relaxed">IP, dispositivo, navegador, páginas acessadas e cookies.</p>
                    </div>
                </div>
            </article>

            <article class="border border-gray-200 bg-white p-6">
                <h2 class="text-xl font-extrabold text-ink mb-3">2. Como usamos seus dados</h2>
                <ul class="grid gap-2 text-sm text-muted leading-relaxed">
                    <li>Processar pedidos, pagamentos, emissão de documentos e entregas.</li>
                    <li>Prestar atendimento, suporte de pós-venda, trocas e acompanhamento de pedidos.</li>
                    <li>Melhorar segurança, navegação, funcionamento da loja e prevenção de fraude.</li>
                    <li>Enviar comunicações sobre pedidos e, quando autorizado, ofertas e novidades.</li>
                </ul>
            </article>

            <article class="border border-gray-200 bg-white p-6">
                <h2 class="text-xl font-extrabold text-ink mb-3">3. Compartilhamento e retenção</h2>
                <p class="text-sm text-muted leading-relaxed">Compartilhamos dados apenas com serviços necessários para operar a loja, como meios de pagamento, transportadoras, ferramentas antifraude, hospedagem e sistemas de atendimento. Guardamos as informações pelo tempo necessário para cumprir finalidades comerciais, fiscais, legais e de segurança.</p>
            </article>

            <article class="border border-gray-200 bg-white p-6">
                <h2 class="text-xl font-extrabold text-ink mb-3">4. Seus direitos e cookies</h2>
                <p class="text-sm text-muted leading-relaxed">Você pode solicitar confirmação de tratamento, acesso, correção, portabilidade, anonimização, exclusão ou revisão de consentimento. Usamos cookies para carrinho, login, segurança, estatísticas e melhoria da experiência; eles podem ser gerenciados no navegador.</p>
            </article>
        </div>
    </div>
</section>
@endsection
