@extends('layouts.app')

@section('title', 'Minha Conta - ' . config('app.name'))

@section('content')
<section class="bg-[#f9f6f3] border-b border-gray-200 py-12">
    <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
        <p class="text-[11px] tracking-[.22em] text-muted uppercase mb-3">Área do Cliente</p>
        <h1 class="title-elegant text-ink" style="font-size:clamp(1.8rem,4vw,3rem)">Minha Conta</h1>
    </div>
</section>

<section class="max-w-[900px] mx-auto px-4 lg:px-8 py-12">
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-xl font-semibold text-ink">Olá, {{ $user->name }}!</h2>
            <p class="text-sm text-muted mt-0.5">{{ $user->email }}</p>
        </div>
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="text-xs text-muted hover:text-red-500 border border-gray-300 px-4 py-2 transition tracking-wide uppercase">
                Sair
            </button>
        </form>
    </div>

    <h3 class="text-xs font-bold tracking-widest text-muted uppercase mb-4">Meus Pedidos</h3>

    @if ($orders->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
            <p class="text-muted mb-4">Você ainda não realizou nenhum pedido.</p>
            <a href="{{ route('store.index') }}" class="inline-flex px-6 py-3 bg-ink text-white text-sm tracking-[.14em] uppercase hover:opacity-90 transition">Ver produtos</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
            <div class="bg-white border border-gray-200 p-5">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-sm font-semibold text-ink">#{{ $order->number }}</p>
                        <p class="text-xs text-muted mt-0.5">{{ $order->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-ink">R$ {{ number_format($order->total, 2, ',', '.') }}</p>
                        <span class="inline-block mt-1 text-[11px] px-2 py-0.5 rounded-full tracking-wide
                            @if($order->status === 'delivered') bg-green-100 text-green-800
                            @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-700 @endif
                        ">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <div class="mt-8 pt-6 border-t border-gray-200 flex flex-wrap gap-3">
        <a href="{{ route('store.contato') }}" class="inline-flex px-5 py-2.5 bg-ink text-white text-sm tracking-[.12em] uppercase hover:opacity-90 transition">Fale Conosco</a>
        <a href="{{ route('store.index') }}" class="inline-flex px-5 py-2.5 border border-gray-300 text-sm tracking-[.12em] uppercase text-muted hover:bg-gray-50 transition">Continuar Comprando</a>
    </div>
</section>
@endsection
