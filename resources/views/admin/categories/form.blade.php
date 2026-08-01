@extends('admin.layout')

@section('title', $category->exists ? 'Editar categoria' : 'Nova categoria')
@section('heading', $category->exists ? 'Editar categoria' : 'Nova categoria')

@section('content')
<div class="panel-card panel-card-body">
    <form method="POST" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categorias.update', $category) : route('admin.categorias.store') }}">
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

        <div class="mb-5 rounded-2xl border border-line bg-slate-50 p-4 sm:p-5">
            <label class="panel-label" for="category_image">Foto da categoria</label>
            <p class="mb-3 text-xs text-slate-500">Esta imagem aparecerá no círculo de categorias da página inicial. Envie JPG, PNG ou WEBP de até 5 MB.</p>

            @if($category->image)
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <img class="h-28 w-28 rounded-full border-2 border-emerald-500 bg-slate-900 object-cover" src="{{ $category->image }}" alt="Foto atual de {{ $category->name }}">
                    <div>
                        <p class="mb-2 text-sm font-semibold text-slate-700">Foto atual</p>
                        <label class="flex items-center gap-2 text-sm text-red-700">
                            <input class="h-4 w-4 rounded border-slate-300" type="checkbox" name="remove_image" value="1">
                            Remover foto atual
                        </label>
                    </div>
                </div>
            @endif

            <input class="panel-input" id="category_image" type="file" name="category_image" accept="image/jpeg,image/png,image/webp">
            <div id="categoryImagePreview" class="mt-4 hidden">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Nova foto</p>
                <img class="h-28 w-28 rounded-full border-2 border-emerald-500 bg-slate-900 object-cover" alt="Prévia da nova foto da categoria">
            </div>
        </div>

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

<script>
document.getElementById('category_image')?.addEventListener('change', function () {
    const preview = document.getElementById('categoryImagePreview');
    const image = preview?.querySelector('img');
    const file = this.files?.[0];

    if (!preview || !image || !file) {
        preview?.classList.add('hidden');
        return;
    }

    image.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
});
</script>
@endsection
