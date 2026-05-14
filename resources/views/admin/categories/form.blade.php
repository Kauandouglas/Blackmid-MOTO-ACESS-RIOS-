@extends('admin.layout')

@section('title', $category->exists ? 'Editar categoria' : 'Nova categoria')
@section('heading', $category->exists ? 'Editar categoria' : 'Nova categoria')

@section('content')
<div class="panel-card panel-card-body">
    <form method="POST" action="{{ $category->exists ? route('admin.categorias.update', $category) : route('admin.categorias.store') }}">
        @csrf
        @if($category->exists)
            @method('PUT')
        @endif

        <label class="panel-label">Nome</label>
        <input class="panel-input mb-4" type="text" name="name" value="{{ old('name', $category->name) }}" required>

        <label class="panel-label">Categoria pai</label>
        <select class="panel-input mb-2" name="parent_id">
            <option value="">Categoria principal</option>
            @foreach($parentCategories as $parentCategory)
                <option value="{{ $parentCategory->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parentCategory->id)>
                    {{ $parentCategory->name }}
                </option>
            @endforeach
        </select>
        <p class="mb-4 text-xs text-slate-500">Deixe como categoria principal por enquanto. Use este campo quando quiser criar uma subcategoria.</p>

        <label class="panel-label">Descricao</label>
        <textarea class="panel-textarea mb-4" name="description" rows="4">{{ old('description', $category->description) }}</textarea>

        <label class="mb-6 flex items-center gap-2 text-sm font-medium text-slate-700">
            <input class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand" type="checkbox" name="active" value="1" {{ old('active', $category->active ?? true) ? 'checked' : '' }}>
            Categoria ativa
        </label>

        <div class="flex flex-wrap gap-2">
            <button class="panel-btn-primary" type="submit">Salvar</button>
            <a class="panel-btn-secondary" href="{{ route('admin.categorias.index') }}">Voltar</a>
        </div>
    </form>
</div>
@endsection
