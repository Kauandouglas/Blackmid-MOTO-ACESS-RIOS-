@extends('admin.layout')

@section('title', 'Menus')
@section('heading', 'Menus')

@section('content')
<div class="mb-4 flex justify-end">
    <a class="panel-btn-primary" href="{{ route('admin.menus.create') }}">Novo menu</a>
</div>

<div class="panel-card overflow-hidden">
    <div class="panel-table-wrap">
    <table class="panel-table">
        <thead class="panel-thead">
        <tr>
            <th class="panel-th">Nome</th>
            <th class="panel-th hidden sm:table-cell">Slug</th>
            <th class="panel-th hidden sm:table-cell">Itens</th>
            <th class="panel-th">Status</th>
            <th class="panel-th text-right">Ações</th>
        </tr>
        </thead>
        <tbody class="panel-table-body">
        @forelse($menus as $menu)
            <tr>
                <td class="panel-td-strong">{{ $menu->name }}</td>
                <td class="panel-td hidden sm:table-cell">{{ $menu->slug }}</td>
                <td class="panel-td hidden sm:table-cell">{{ $menu->items_count }}</td>
                <td class="panel-td">
                    <span class="{{ $menu->active ? 'panel-badge-green' : 'panel-badge-gray' }}">{{ $menu->active ? 'Ativo' : 'Inativo' }}</span>
                </td>
                <td class="panel-td text-right space-x-2">
                    <a class="panel-btn-secondary px-3 py-2 text-xs" href="{{ route('admin.menus.edit', $menu) }}">Editar</a>
                    <form class="inline" method="POST" action="{{ route('admin.menus.destroy', $menu) }}">
                        @csrf
                        @method('DELETE')
                        <button class="panel-btn-danger px-3 py-2 text-xs" type="submit" onclick="return confirm('Excluir menu?')">Excluir</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="panel-td py-8 text-center text-slate-500">Nenhum menu encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="border-t border-line px-5 py-4">
    {{ $menus->links() }}
    </div>
</div>
@endsection
