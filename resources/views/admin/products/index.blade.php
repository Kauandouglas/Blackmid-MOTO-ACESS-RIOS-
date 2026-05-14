@extends('admin.layout')

@section('title', 'Produtos')
@section('heading', 'Produtos')

@section('content')
<div class="panel-card mb-3 sm:mb-4 p-3 sm:p-4">
    <form method="GET" class="flex flex-col gap-2 sm:gap-3 md:flex-row md:items-center">
        <input class="panel-input" type="text" name="q" value="{{ $search }}" placeholder="Buscar produto por nome ou slug">
        <button class="panel-btn-secondary" type="submit">Filtrar</button>
        <a class="panel-btn-primary" href="{{ route('admin.produtos.create') }}">Novo produto</a>
    </form>
</div>

<div class="panel-card overflow-hidden">
    <div class="panel-table-wrap">
    <table class="panel-table">
        <thead class="panel-thead">
        <tr>
            <th class="panel-th">Produto</th>
            <th class="panel-th hidden lg:table-cell">Categoria</th>
            <th class="panel-th hidden xl:table-cell">SKU</th>
            <th class="panel-th">Preco</th>
            <th class="panel-th hidden sm:table-cell">Estoque</th>
            <th class="panel-th hidden xl:table-cell">Home</th>
            <th class="panel-th hidden sm:table-cell">Status</th>
            <th class="panel-th text-right">Acoes</th>
        </tr>
        </thead>
        <tbody class="panel-table-body">
        @forelse($products as $product)
            <tr>
                <td class="panel-td-strong">
                    <span class="block">{{ $product->name }}</span>
                    <span class="block text-xs text-muted font-normal lg:hidden">{{ $product->category?->name ?? '' }}</span>
                    <span class="inline sm:hidden">
                        <span class="{{ $product->active ? 'panel-badge-green' : 'panel-badge-gray' }} mt-1">{{ $product->active ? 'Ativo' : 'Inativo' }}</span>
                    </span>
                </td>
                <td class="panel-td hidden lg:table-cell">{{ $product->category?->name ?? '-' }}</td>
                <td class="panel-td hidden xl:table-cell">{{ $product->bling_code ?: '-' }}</td>
                <td class="panel-td">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                <td class="panel-td hidden sm:table-cell">{{ ($product->track_stock ?? true) ? $product->stock : 'infinito' }}</td>
                <td class="panel-td hidden xl:table-cell">
                    <div class="flex flex-wrap gap-1">
                        @if($product->highlight_best_sellers ?? false)
                            <span class="panel-badge-green">Mais Vendidos</span>
                        @endif
                        @if($product->highlight_launches ?? false)
                            <span class="panel-badge-blue">Lancamentos</span>
                        @endif
                        @if(!($product->highlight_best_sellers ?? false) && !($product->highlight_launches ?? false))
                            <span class="panel-badge-gray">-</span>
                        @endif
                    </div>
                </td>
                <td class="panel-td hidden sm:table-cell">
                    <span class="{{ $product->active ? 'panel-badge-green' : 'panel-badge-gray' }}">{{ $product->active ? 'Ativo' : 'Inativo' }}</span>
                </td>
                <td class="panel-td text-right">
                    <div class="flex items-center justify-end gap-1 sm:gap-2">
                        <a class="panel-btn-secondary px-2.5 py-1.5 text-xs sm:px-3 sm:py-2" href="{{ route('admin.produtos.edit', $product) }}">Editar</a>
                        <form class="inline" method="POST" action="{{ route('admin.produtos.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                            <button class="panel-btn-danger px-2.5 py-1.5 text-xs sm:px-3 sm:py-2" type="submit" onclick="return confirm('Excluir produto?')">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="panel-td py-8 text-center text-slate-500">Nenhum produto encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="border-t border-line px-5 py-4">
    {{ $products->links() }}
    </div>
</div>
@endsection
