@extends('admin.layout')
@section('title', 'Carrinhos Abandonados')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-ink">Carrinhos Abandonados</h1>
            <p class="text-sm text-muted mt-1">Clientes que iniciaram o checkout mas não finalizaram a compra (após 6 horas).</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.abandoned-carts.index', ['filter' => 'abandoned']) }}"
           class="panel-badge {{ $filter === 'abandoned' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
            🛒 Abandonados ({{ $counts['abandoned'] }})
        </a>
        <a href="{{ route('admin.abandoned-carts.index', ['filter' => 'pending']) }}"
           class="panel-badge {{ $filter === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
            ⏳ Pendentes ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('admin.abandoned-carts.index', ['filter' => 'converted']) }}"
           class="panel-badge {{ $filter === 'converted' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition">
            ✅ Convertidos ({{ $counts['converted'] }})
        </a>
    </div>

    {{-- Tabela --}}
    <div class="panel-card">
        @if($carts->isEmpty())
            <div class="panel-card-body text-center py-16">
                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <p class="text-muted text-sm">
                    @if($filter === 'abandoned')
                        Nenhum carrinho abandonado no momento.
                    @elseif($filter === 'pending')
                        Nenhum checkout em andamento.
                    @else
                        Nenhuma conversão registrada.
                    @endif
                </p>
            </div>
        @else
            <div class="panel-table-wrap">
                <table class="panel-table">
                    <thead class="panel-thead">
                        <tr>
                            <th class="panel-th">Cliente</th>
                            <th class="panel-th hidden md:table-cell">E-mail</th>
                            <th class="panel-th hidden xl:table-cell">Telefone</th>
                            <th class="panel-th hidden sm:table-cell text-center">Itens</th>
                            <th class="panel-th text-right">Subtotal</th>
                            <th class="panel-th hidden lg:table-cell">Data</th>
                            <th class="panel-th">Status</th>
                            <th class="panel-th text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="panel-table-body">
                        @foreach($carts as $cart)
                        <tr>
                            <td class="panel-td-strong">
                                <span class="block whitespace-nowrap">{{ $cart->fullName() ?: '—' }}</span>
                                <span class="block text-xs text-muted font-normal md:hidden truncate max-w-[140px]">{{ $cart->customer_email }}</span>
                            </td>
                            <td class="panel-td hidden md:table-cell">
                                <a href="mailto:{{ $cart->customer_email }}" class="text-brand hover:underline">{{ $cart->customer_email }}</a>
                            </td>
                            <td class="panel-td hidden xl:table-cell whitespace-nowrap">{{ $cart->customer_phone ?: '—' }}</td>
                            <td class="panel-td hidden sm:table-cell text-center">{{ $cart->items_count }}</td>
                            <td class="panel-td text-right font-semibold">R$ {{ number_format($cart->subtotal, 2, ',', '.') }}</td>
                            <td class="panel-td hidden lg:table-cell whitespace-nowrap text-muted">{{ $cart->created_at->format('d/m/Y H:i') }}</td>
                            <td class="panel-td">
                                @if($cart->converted_at)
                                    <span class="panel-badge-green">Convertido</span>
                                @elseif($cart->isAbandoned())
                                    <span class="panel-badge bg-rose-100 text-rose-700">Abandonado</span>
                                @else
                                    <span class="panel-badge-amber">Pendente</span>
                                @endif
                            </td>
                            <td class="panel-td text-center">
                                <a href="{{ route('admin.abandoned-carts.show', $cart) }}" class="text-brand hover:underline text-xs font-bold">Ver</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($carts->hasPages())
                <div class="panel-card-body border-t border-line">
                    {{ $carts->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
