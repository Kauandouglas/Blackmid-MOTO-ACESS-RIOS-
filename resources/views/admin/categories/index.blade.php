@extends('admin.layout')

@section('title', 'Categorias')
@section('heading', 'Categorias')

@section('content')
<div class="mb-4 flex justify-end">
    <a class="panel-btn-primary" href="{{ route('admin.categorias.create') }}">Nova categoria</a>
</div>

<div class="panel-card overflow-hidden">
    <div class="panel-table-wrap">
    <table class="panel-table">
        <thead class="panel-thead">
        <tr>
            <th class="panel-th">Nome</th>
            <th class="panel-th hidden sm:table-cell">Slug</th>
            <th class="panel-th hidden md:table-cell">Tipo</th>
            <th class="panel-th">Status</th>
            <th class="panel-th text-right">Acoes</th>
        </tr>
        </thead>
        <tbody class="panel-table-body">
        @forelse($categories as $category)
            <tr>
                <td class="panel-td-strong">{{ $category->name }}</td>
                <td class="panel-td hidden sm:table-cell">{{ $category->slug }}</td>
                <td class="panel-td hidden md:table-cell">
                    {{ $category->parent ? 'Subcategoria de '.$category->parent->name : 'Principal' }}
                </td>
                <td class="panel-td">
                    <span class="{{ $category->active ? 'panel-badge-green' : 'panel-badge-gray' }}">{{ $category->active ? 'Ativa' : 'Inativa' }}</span>
                </td>
                <td class="panel-td text-right space-x-2">
                    <a class="panel-btn-secondary px-3 py-2 text-xs" href="{{ route('admin.categorias.edit', $category) }}">Editar</a>
                    <form class="inline" method="POST" action="{{ route('admin.categorias.destroy', $category) }}">
                        @csrf
                        @method('DELETE')
                        <button class="panel-btn-danger px-3 py-2 text-xs" type="submit" onclick="return confirm('Excluir categoria?')">Excluir</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="panel-td py-8 text-center text-slate-500">Nenhuma categoria cadastrada.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="border-t border-line px-5 py-4">
    {{ $categories->links() }}
    </div>
</div>
@endsection
