@extends('admin.layout')

@section('title', 'Pedidos')
@section('heading', 'Pedidos')

@section('content')
<div class="panel-card mb-3 sm:mb-4 p-3 sm:p-4">
    <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
        <select class="panel-select w-full sm:w-64" name="status">
            <option value="">Todos os status</option>
            @foreach(['received','awaiting_payment','paid','processing','shipped','delivered','cancelled','payment_cancelled'] as $opt)
                <option value="{{ $opt }}" {{ $status === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <button class="panel-btn-secondary" type="submit">Filtrar</button>
    </form>
</div>

<div class="panel-card overflow-hidden">
    <div class="panel-table-wrap">
    <table class="panel-table">
        <thead class="panel-thead">
        <tr>
            <th class="panel-th">Numero</th>
            <th class="panel-th hidden md:table-cell">Cliente</th>
            <th class="panel-th">Status</th>
            <th class="panel-th hidden sm:table-cell">Pago</th>
            <th class="panel-th">Total</th>
            <th class="panel-th text-right">Acao</th>
        </tr>
        </thead>
        <tbody class="panel-table-body">
        @forelse($orders as $order)
            <tr>
                <td class="panel-td-strong">
                    <span class="block">{{ $order->number }}</span>
                    <span class="block text-xs text-muted font-normal md:hidden">{{ $order->customer_name }}</span>
                </td>
                <td class="panel-td hidden md:table-cell">{{ $order->customer_name }}</td>
                <td class="panel-td">
                    <span class="panel-badge-gray">{{ $order->status }}</span>
                    <span class="block mt-1 sm:hidden">{{ $order->paid ? 'Sim' : 'Nao' }}</span>
                </td>
                <td class="panel-td hidden sm:table-cell">
                    <span class="{{ $order->paid ? 'panel-badge-green' : 'panel-badge-amber' }}">{{ $order->paid ? 'Sim' : 'Nao' }}</span>
                </td>
                <td class="panel-td">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</td>
                <td class="panel-td text-right"><a class="panel-btn-secondary px-3 py-2 text-xs" href="{{ route('admin.orders.show', $order) }}">Detalhes</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="panel-td py-8 text-center text-slate-500">Nenhum pedido encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="border-t border-line px-5 py-4">
    {{ $orders->links() }}
    </div>
</div>
@endsection
