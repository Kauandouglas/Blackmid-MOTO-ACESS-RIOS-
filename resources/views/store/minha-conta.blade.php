@extends('layouts.app')

@section('title', 'Minha Conta - ' . config('app.name'))

@section('content')
@php
    $statusLabels = [
        'received' => 'Recebido',
        'awaiting_payment' => 'Aguardando pagamento',
        'paid' => 'Pago',
        'processing' => 'Em preparacao',
        'shipped' => 'Enviado',
        'delivered' => 'Entregue',
        'cancelled' => 'Cancelado',
        'payment_cancelled' => 'Pagamento cancelado',
    ];
@endphp

<section class="account-page">
    <div class="container">
        <header class="account-title">
            <div>
                <span class="eyebrow">Area do Cliente</span>
                <h1>Minha Conta</h1>
                <p>Acompanhe seus pedidos, confirme seus dados e fale com o atendimento quando precisar.</p>
            </div>

            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="account-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Sair</span>
                </button>
            </form>
        </header>

        @if (session('success'))
            <div class="store-alert store-alert--success account-alert">{{ session('success') }}</div>
        @endif

        <div class="account-layout">
            <aside class="account-card account-profile">
                <span class="account-avatar"><i class="fa-regular fa-user"></i></span>
                <div>
                    <span class="account-card__label">Cliente logado</span>
                    <h2>Ola, {{ $user->name }}!</h2>
                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                </div>

                <div class="account-profile__meta">
                    <div>
                        <span>Pedidos</span>
                        <strong>{{ $orders->count() }}</strong>
                    </div>
                    <div>
                        <span>Cadastro</span>
                        <strong>{{ optional($user->created_at)->format('d/m/Y') ?? '-' }}</strong>
                    </div>
                </div>

                <div class="account-actions">
                    <a href="{{ route('store.contato') }}" class="btn btn-outline">
                        <i class="fa-regular fa-circle-question"></i>
                        <span>Atendimento</span>
                    </a>
                    <a href="{{ route('store.index') }}" class="btn btn-green">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>Comprar</span>
                    </a>
                </div>
            </aside>

            <section class="account-card account-orders">
                <div class="account-section-head">
                    <div>
                        <span class="account-card__label">Historico</span>
                        <h2>Meus Pedidos</h2>
                    </div>
                    @if ($orders->isNotEmpty())
                        <span class="account-count">{{ $orders->count() }} {{ $orders->count() === 1 ? 'pedido' : 'pedidos' }}</span>
                    @endif
                </div>

                @if ($orders->isEmpty())
                    <div class="account-empty">
                        <span><i class="fa-solid fa-box-open"></i></span>
                        <h3>Nenhum pedido por aqui</h3>
                        <p>Quando voce fizer uma compra, ela aparecera aqui com data, total e status.</p>
                        <a href="{{ route('store.index') }}" class="btn btn-green">Ver produtos</a>
                    </div>
                @else
                    <div class="account-order-list">
                        @foreach ($orders as $order)
                            @php($statusClass = match ($order->status) {
                                'delivered' => 'account-status--success',
                                'shipped' => 'account-status--info',
                                'paid', 'processing', 'received' => 'account-status--active',
                                'cancelled', 'payment_cancelled' => 'account-status--danger',
                                default => 'account-status--muted',
                            })
                            <article class="account-order">
                                <div class="account-order__main">
                                    <span class="account-order__number">#{{ $order->number }}</span>
                                    <span class="account-order__date">{{ $order->created_at->format('d/m/Y') }}</span>
                                </div>

                                <div class="account-order__details">
                                    <span class="account-order__total">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</span>
                                    <span class="account-status {{ $statusClass }}">
                                        {{ $statusLabels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</section>
@endsection
