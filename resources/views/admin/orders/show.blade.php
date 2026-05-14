@extends('admin.layout')

@section('title', 'Pedido ' . $order->number)
@section('heading', 'Pedido ' . $order->number)

@section('content')
<div class="grid gap-4 md:grid-cols-2 mb-4">
    <div class="panel-card panel-card-body">
        <h3 class="panel-section-title mb-4">Dados do cliente</h3>
        <div class="space-y-2 text-sm text-slate-700">
            <p><strong>Nome:</strong> {{ $order->customer_name }}</p>
            <p><strong>Email:</strong> {{ $order->customer_email }}</p>
            <p><strong>Telefone:</strong> {{ $order->customer_phone }}</p>
            <p><strong>CPF/CNPJ:</strong> {{ $order->customer_document ?: '-' }}</p>
            <p><strong>Endereço:</strong><br>{{ $order->shipping_address }}</p>
            <p><strong>Pagamento:</strong> {{ $order->payment_provider ?? '-' }}</p>
            <p><strong>Referência:</strong> {{ $order->payment_reference ?? '-' }}</p>
        </div>
    </div>

    <div class="panel-card panel-card-body">
        <h3 class="panel-section-title mb-4">Status do pedido</h3>
        <form method="POST" action="{{ route('admin.orders.update', $order) }}">
            @csrf
            @method('PATCH')

            <label class="panel-label">Status</label>
            <select class="panel-select mb-4" name="status" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>

            <label class="mb-5 flex items-center gap-2 text-sm font-medium text-slate-700">
                <input class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand" type="checkbox" name="paid" value="1" {{ $order->paid ? 'checked' : '' }}>
                Pedido pago
            </label>

            <div class="flex flex-wrap gap-2">
                <button class="panel-btn-primary" type="submit">Atualizar pedido</button>
                <a class="panel-btn-secondary" href="{{ route('admin.orders.index') }}">Voltar</a>
            </div>
        </form>
    </div>
</div>

<div class="panel-card overflow-hidden">
    <div class="border-b border-line px-5 py-4">
        <h3 class="panel-section-title">Itens</h3>
    </div>
    <div class="panel-table-wrap">
    <table class="panel-table">
        <thead class="panel-thead">
        <tr>
            <th class="panel-th">Produto</th>
            <th class="panel-th">Qtd</th>
            <th class="panel-th hidden sm:table-cell">Preço unit.</th>
            <th class="panel-th text-right">Subtotal</th>
        </tr>
        </thead>
        <tbody class="panel-table-body">
        @foreach($order->items as $item)
            <tr>
                <td class="panel-td-strong">
                    <span class="block">{{ $item->product_name }}</span>
                    <span class="block text-xs text-muted font-normal sm:hidden">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }} cada</span>
                </td>
                <td class="panel-td">{{ $item->quantity }}</td>
                <td class="panel-td hidden sm:table-cell">R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                <td class="panel-td text-right">R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    <div class="space-y-1 border-t border-line bg-cloud p-3 sm:p-5 text-sm">
        <p><strong>Subtotal:</strong> R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</p>
        <p><strong>Frete:</strong> R$ {{ number_format((float) $order->shipping_fee, 2, ',', '.') }}</p>
        @php $discount = max(0, ((float) $order->subtotal + (float) $order->shipping_fee) - (float) $order->total); @endphp
        @if($discount > 0)
            <p><strong>Desconto:</strong> -R$ {{ number_format($discount, 2, ',', '.') }}</p>
        @endif
        <p class="text-base font-bold"><strong>Total:</strong> R$ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
    </div>
</div>
@endsection
