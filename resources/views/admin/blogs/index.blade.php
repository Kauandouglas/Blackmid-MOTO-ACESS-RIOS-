@extends('admin.layout')

@section('title', 'Blog')
@section('heading', 'Blog')

@section('content')
<div class="panel-card mb-3 sm:mb-4 p-3 sm:p-4">
    <form method="GET" class="flex flex-col gap-2 sm:gap-3 md:flex-row md:items-center">
        <input class="panel-input" type="text" name="q" value="{{ $search }}" placeholder="Buscar por título, slug ou categoria">
        <button class="panel-btn-secondary" type="submit">Filtrar</button>
        <a class="panel-btn-primary" href="{{ route('admin.blogs.create') }}">Novo post</a>
    </form>
</div>

<div class="panel-card overflow-hidden">
    <div class="panel-table-wrap">
        <table class="panel-table">
            <thead class="panel-thead">
                <tr>
                    <th class="panel-th">Título</th>
                    <th class="panel-th hidden md:table-cell">Categoria</th>
                    <th class="panel-th hidden lg:table-cell">Publicação</th>
                    <th class="panel-th">Status</th>
                    <th class="panel-th text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="panel-table-body">
                @forelse($posts as $post)
                    <tr>
                        <td class="panel-td-strong">
                            <div>{{ $post->title }}</div>
                            <div class="text-xs text-slate-500 mt-1">/{{ $post->slug }}</div>
                            <div class="text-xs text-muted mt-0.5 md:hidden">{{ $post->category ?: '' }}</div>
                        </td>
                        <td class="panel-td hidden md:table-cell">{{ $post->category ?: '-' }}</td>
                        <td class="panel-td hidden lg:table-cell">{{ optional($post->published_at)->format('d/m/Y H:i') ?: '-' }}</td>
                        <td class="panel-td">
                            <span class="{{ $post->active ? 'panel-badge-green' : 'panel-badge-gray' }}">{{ $post->active ? 'Publicado' : 'Oculto' }}</span>
                        </td>
                        <td class="panel-td text-right space-x-2">
                            <a class="panel-btn-secondary px-3 py-2 text-xs" href="{{ route('admin.blogs.edit', $post) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('admin.blogs.destroy', $post) }}">
                                @csrf
                                @method('DELETE')
                                <button class="panel-btn-danger px-3 py-2 text-xs" type="submit" onclick="return confirm('Excluir este post?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="panel-td py-8 text-center text-slate-500">Nenhum post cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-line px-5 py-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection
