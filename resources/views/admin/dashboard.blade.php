@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="mb-4 sm:mb-6 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
    <div class="panel-card panel-card-body">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">Produtos</p>
                <p class="mt-2 text-3xl font-extrabold text-ink">{{ $stats['products'] }}</p>
            </div>
            <span class="panel-badge-blue">Catálogo</span>
        </div>
    </div>
    <div class="panel-card panel-card-body">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">Categorias</p>
                <p class="mt-2 text-3xl font-extrabold text-ink">{{ $stats['categories'] }}</p>
            </div>
            <span class="panel-badge-gray">Organização</span>
        </div>
    </div>
    <div class="panel-card panel-card-body">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">Pedidos</p>
                <p class="mt-2 text-3xl font-extrabold text-ink">{{ $stats['orders'] }}</p>
            </div>
            <span class="panel-badge-amber">Operação</span>
        </div>
    </div>
    <div class="panel-card panel-card-body bg-[linear-gradient(135deg,#1463ff,#4f8cff)] text-white shadow-float border-0">
        <p class="text-sm font-semibold text-white/80">Receita paga</p>
        <p class="mt-2 text-3xl font-extrabold">R$ {{ number_format($stats['revenue'], 2, ',', '.') }}</p>
        <p class="mt-2 text-sm text-white/80">Total confirmado em pedidos pagos.</p>
    </div>
</div>

<div class="panel-card overflow-hidden">
    <div class="flex items-center justify-between border-b border-line px-5 py-4">
        <h3 class="panel-section-title">Últimos pedidos</h3>
        <span class="panel-badge-blue">Últimos movimentos</span>
    </div>

    <div class="panel-table-wrap">
    <table class="panel-table">
        <thead class="panel-thead">
        <tr>
            <th class="panel-th">Número</th>
            <th class="panel-th hidden sm:table-cell">Cliente</th>
            <th class="panel-th">Status</th>
            <th class="panel-th">Total</th>
            <th class="panel-th text-right">Ação</th>
        </tr>
        </thead>
        <tbody class="panel-table-body">
        @forelse ($latestOrders as $order)
            <tr>
                <td class="panel-td-strong">
                    <span class="block">{{ $order->number }}</span>
                    <span class="block text-xs text-muted font-normal sm:hidden">{{ $order->customer_name }}</span>
                </td>
                <td class="panel-td hidden sm:table-cell">{{ $order->customer_name }}</td>
                <td class="panel-td"><span class="panel-badge-gray">{{ $order->status }}</span></td>
                <td class="panel-td">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</td>
                <td class="panel-td text-right"><a class="panel-btn-secondary px-3 py-2 text-xs" href="{{ route('admin.orders.show', $order) }}">Abrir</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="panel-td py-8 text-center text-slate-500">Nenhum pedido encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
