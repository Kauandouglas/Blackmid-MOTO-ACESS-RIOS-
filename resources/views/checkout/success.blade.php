@extends('layouts.app')

@section('content')
<section class="max-w-2xl mx-auto px-4 py-24 text-center">
    <div class="bg-white border border-gray-200 rounded-lg p-10 lg:p-14">
        <svg class="w-16 h-16 mx-auto mb-6 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <p class="text-xs uppercase tracking-[.2em] text-muted mb-3">Pedido realizado com sucesso</p>
        <h1 class="font-display text-3xl lg:text-4xl text-ink font-bold mb-4">Obrigado pela sua compra!</h1>
        <p class="text-muted mb-1">Seu pedido foi registrado com o numero <strong class="text-ink">{{ $order->number }}</strong>.</p>
        <p class="text-muted mb-1">Total do pedido: <strong class="text-ink">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></p>
        <p class="text-muted mb-8">Pagamento: <strong class="text-ink">{{ strtoupper($order->payment_provider ?? 'N/A') }}</strong> @if($order->payment_reference) · Ref: <strong class="text-ink">{{ $order->payment_reference }}</strong>@endif</p>
        <a href="{{ route('store.index') }}" class="inline-block bg-ink text-white px-10 py-3.5 text-sm tracking-[.15em] font-medium hover:bg-gray-800 transition">VOLTAR PARA LOJA</a>
    </div>
</section>
@endsection
    @push('pixel_events')
    @if(config('store.pixel.facebook') && ($pixelFireOnce ?? false))
    <script>
    if (typeof fbq === 'function') {
        fbq('track', 'Purchase', {
            value: {{ (float) $order->total }},
            currency: '{{ strtoupper((string) config('store.pixel.facebook_currency', 'BRL')) }}',
            content_type: 'product',
            content_ids: @json($order->items->pluck('product_id')->filter()->map(fn ($id) => (string) $id)->values()),
            num_items: {{ $order->items->sum('quantity') }}
        });
    }
    </script>
    @endif
    @endpush
