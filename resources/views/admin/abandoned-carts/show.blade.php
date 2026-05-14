@extends('admin.layout')
@section('title', 'Carrinho Abandonado #' . $cart->id)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
        <a href="{{ route('admin.abandoned-carts.index') }}" class="panel-btn-secondary text-xs self-start">&larr; Voltar</a>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-extrabold text-ink">Carrinho #{{ $cart->id }}</h1>
            <p class="text-sm text-muted mt-0.5">
                Criado em {{ $cart->created_at->format('d/m/Y H:i') }}
                @if($cart->converted_at)
                    — <span class="text-emerald-600 font-semibold">Convertido em {{ $cart->converted_at->format('d/m/Y H:i') }}</span>
                @elseif($cart->isAbandoned())
                    — <span class="text-rose-600 font-semibold">Abandonado há {{ $cart->created_at->diffForHumans() }}</span>
                @else
                    — <span class="text-amber-600 font-semibold">Aguardando ({{ $cart->created_at->diffForHumans() }})</span>
                @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Dados do Cliente --}}
        <div class="panel-card lg:col-span-1">
            <div class="panel-card-body space-y-4">
                <h2 class="panel-section-title">Cliente</h2>

                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-muted mb-1">Nome</p>
                    <p class="text-sm text-ink font-semibold">{{ $cart->fullName() ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-muted mb-1">E-mail</p>
                    <a href="mailto:{{ $cart->customer_email }}" class="text-sm text-brand hover:underline">{{ $cart->customer_email }}</a>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-muted mb-1">Telefone</p>
                    <p class="text-sm text-ink">{{ $cart->customer_phone ?: '—' }}</p>
                </div>

                @if($cart->order_id)
                <div class="border-t border-line pt-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-muted mb-1">Pedido</p>
                    <a href="{{ route('admin.orders.show', $cart->order_id) }}" class="text-sm text-brand hover:underline font-semibold">
                        Ver pedido #{{ $cart->order_id }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Itens do Carrinho --}}
        <div class="panel-card lg:col-span-2">
            <div class="panel-card-body">
                <h2 class="panel-section-title mb-4">Itens do Carrinho</h2>

                <div class="space-y-3">
                    @forelse($cart->cart_items ?? [] as $item)
                    <div class="flex items-center gap-4 rounded-2xl border border-line p-3">
                        @if(!empty($item['image']))
                        <img src="{{ $item['image'] }}" alt="" class="h-14 w-14 rounded-xl object-cover border border-line flex-shrink-0">
                        @else
                        <div class="h-14 w-14 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-ink truncate">{{ $item['name'] ?? 'Produto' }}</p>
                            <p class="text-xs text-muted">
                                Qtd: {{ $item['quantity'] ?? 1 }}
                                @if(!empty($item['size'])) &middot; Tam: {{ $item['size'] }} @endif
                                @if(!empty($item['color'])) &middot; Cor: {{ $item['color'] }} @endif
                            </p>
                        </div>
                        <p class="text-sm font-bold text-ink flex-shrink-0">R$ {{ number_format($item['line_total'] ?? 0, 2, ',', '.') }}</p>
                    </div>
                    @empty
                    <p class="text-muted text-sm">Nenhum item salvo.</p>
                    @endforelse
                </div>

                <div class="border-t border-line mt-4 pt-4 flex justify-end">
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-widest text-muted mb-1">Subtotal</p>
                        <p class="text-xl font-extrabold text-ink">R$ {{ number_format($cart->subtotal, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
