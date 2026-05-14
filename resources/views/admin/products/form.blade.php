@extends('admin.layout')

@section('title', $product->exists ? 'Editar produto' : 'Novo produto')
@section('heading', $product->exists ? 'Editar produto' : 'Novo produto')

@section('content')
{{-- Quill rich text editor --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">
@php
    $existingImages = collect(array_merge(
        $product->image ? [$product->image] : [],
        $product->gallery ?? []
    ))->filter()->values();
    $coverChoice = old('cover_choice', $product->image ? 'existing:'.$product->image : '');
    $removedExistingImages = collect(old('remove_existing_images', []));
    $sizeSuggestions = ['PP', 'P', 'M', 'G', 'GG', 'XG', 'Único', '34', '36', '38', '40', '42'];
    $colorSuggestions = ['Preto', 'Branco', 'Bege', 'Azul', 'Verde', 'Rosa', 'Vermelho', 'Marrom', 'Cinza', 'Off white'];
    $trackStock = (bool) old('track_stock', $product->exists ? $product->track_stock : false);
    $selectedCategoryIds = collect(old('category_ids', $product->exists
        ? (($product->relationLoaded('categories') ? $product->categories->pluck('id')->all() : []) ?: ($product->category_id ? [$product->category_id] : []))
        : []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $variantStocks = collect(old('variant_stocks', $product->exists
        ? ($product->relationLoaded('variants')
            ? $product->variants->map(fn ($variant) => [
                'size' => $variant->size,
                'color' => $variant->color,
                'stock' => (int) $variant->stock,
            ])->all()
            : [])
        : []))
        ->values()
        ->all();
@endphp

<div class="panel-card panel-card-body">
    <form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.produtos.update', $product) : route('admin.produtos.store') }}" class="space-y-6">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        <div class="rounded-2xl border border-blue-100 bg-blue-50/80 px-4 py-3 text-sm text-blue-900">
            O slug é gerado automaticamente pelo nome do produto e nunca repete.
        </div>

        <div>
            <label class="panel-label">Nome</label>
            <input class="panel-input" type="text" name="name" value="{{ old('name', $product->name) }}" required>
        </div>

        <div class="rounded-2xl border border-line bg-slate-50 p-4">
            <label class="panel-label">Categorias</label>
            <p class="mb-3 text-xs text-slate-500">Selecione uma ou mais categorias para este produto.</p>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $category)
                    <label class="block cursor-pointer">
                        <input
                            class="peer sr-only"
                            type="checkbox"
                            name="category_ids[]"
                            value="{{ $category->id }}"
                            {{ in_array((string) $category->id, $selectedCategoryIds, true) ? 'checked' : '' }}
                        >
                        <span class="flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-brand hover:bg-blue-50 hover:text-brand peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-soft">
                            {{ $category->name }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('category_ids')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-3 sm:gap-4 grid-cols-2 md:grid-cols-3">
            <div>
                <label class="panel-label">Preço (R$)</label>
                <input class="panel-input" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required>
            </div>
            <div>
                <label class="panel-label">Peso Liquido (g)</label>
                <input class="panel-input" type="number" min="1" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams ?? 300) }}" required>
            </div>
            <div>
                <label class="panel-label">Codigo (SKU)</label>
                <input class="panel-input bg-slate-50" type="text" value="{{ $product->bling_code ?: '-' }}" readonly>
            </div>
        </div>

        <div class="rounded-3xl border border-line bg-slate-50 p-5">
            <h3 class="text-base font-bold text-ink">Logistica</h3>
            <p class="mt-1 text-sm text-slate-500">Esses dados podem vir do Bling e ajudam no frete e conferencia operacional.</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="panel-label">Peso Bruto (g)</label>
                    <input class="panel-input" type="number" min="1" name="gross_weight_grams" value="{{ old('gross_weight_grams', $product->gross_weight_grams) }}" placeholder="Ex: 500">
                </div>
                <div>
                    <label class="panel-label">Largura (cm)</label>
                    <input class="panel-input" type="number" step="0.01" min="0" name="width_cm" value="{{ old('width_cm', $product->width_cm) }}" placeholder="Ex: 20">
                </div>
                <div>
                    <label class="panel-label">Altura (cm)</label>
                    <input class="panel-input" type="number" step="0.01" min="0" name="height_cm" value="{{ old('height_cm', $product->height_cm) }}" placeholder="Ex: 10">
                </div>
                <div>
                    <label class="panel-label">Profundidade (cm)</label>
                    <input class="panel-input" type="number" step="0.01" min="0" name="depth_cm" value="{{ old('depth_cm', $product->depth_cm) }}" placeholder="Ex: 30">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-line bg-slate-50 p-5">
            <h3 class="text-base font-bold text-ink">Inventário</h3>
            <p class="mt-1 text-sm text-slate-500">Defina como esse produto será vendido. <strong>Infinito</strong> já vem marcado para facilitar.</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Modo de inventário">
                <label class="block cursor-pointer">
                    <input class="peer sr-only" type="radio" name="track_stock" value="0" {{ ! $trackStock ? 'checked' : '' }}>
                    <span class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition hover:border-brand hover:bg-blue-50 peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-soft">
                        <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full border border-current">
                            <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                        </span>
                        <span>
                            <span class="block font-semibold">Infinito (recomendado)</span>
                            <span class="block text-xs opacity-80">Vende sem limitar quantidade em estoque.</span>
                        </span>
                    </span>
                </label>

                <label class="block cursor-pointer">
                    <input class="peer sr-only" type="radio" name="track_stock" value="1" {{ $trackStock ? 'checked' : '' }}>
                    <span class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition hover:border-brand hover:bg-blue-50 peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-soft">
                        <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full border border-current">
                            <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                        </span>
                        <span>
                            <span class="block font-semibold">Limitado</span>
                            <span class="block text-xs opacity-80">Controla quantidade e bloqueia quando esgotar.</span>
                        </span>
                    </span>
                </label>
            </div>

            <div id="stockLimitedField" class="mt-4">
                <div id="stockSingleField" class="hidden">
                <label class="panel-label">Estoque</label>
                <input class="panel-input" type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}">
                </div>

                <div id="variantStockPanel" class="mt-4 rounded-2xl border border-line bg-white p-4">
                    <div class="mb-3">
                        <h4 class="text-sm font-bold text-ink">Estoque por variação (opcional)</h4>
                        <p class="mt-1 text-xs text-slate-500">Use tamanho e cor. A quantidade por variação aparece apenas no modo Limitado.</p>
                    </div>

                    <div id="variantRows" class="space-y-2">
                        @forelse($variantStocks as $index => $variant)
                            <div class="grid gap-2 sm:grid-cols-[1fr_1fr_120px_auto]" data-variant-row>
                                <input class="panel-input" type="text" name="variant_stocks[{{ $index }}][size]" placeholder="Tamanho (ex: P)" value="{{ $variant['size'] ?? '' }}">
                                <input class="panel-input" type="text" name="variant_stocks[{{ $index }}][color]" placeholder="Cor (ex: Preto)" value="{{ $variant['color'] ?? '' }}">
                                <div data-variant-stock-col>
                                    <input class="panel-input" type="number" min="0" max="99999" name="variant_stocks[{{ $index }}][stock]" placeholder="Qtd" value="{{ (int) ($variant['stock'] ?? 0) }}">
                                </div>
                                <button class="panel-btn-secondary" type="button" data-remove-variant>Remover</button>
                            </div>
                        @empty
                            <div class="grid gap-2 sm:grid-cols-[1fr_1fr_120px_auto]" data-variant-row>
                                <input class="panel-input" type="text" name="variant_stocks[0][size]" placeholder="Tamanho (ex: P)">
                                <input class="panel-input" type="text" name="variant_stocks[0][color]" placeholder="Cor (ex: Preto)">
                                <div data-variant-stock-col>
                                    <input class="panel-input" type="number" min="0" max="99999" name="variant_stocks[0][stock]" placeholder="Qtd" value="0">
                                </div>
                                <button class="panel-btn-secondary" type="button" data-remove-variant>Remover</button>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <button class="panel-btn-secondary self-start" id="addVariantRow" type="button">+ Adicionar variação</button>
                        <span class="text-xs text-slate-500">O estoque total é somado automaticamente pelas variações.</span>
                    </div>

                    <div class="mt-4 grid gap-3 lg:grid-cols-2">
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Sugestões de tamanho</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($sizeSuggestions as $sizeSuggestion)
                                    <button class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-brand hover:text-brand" type="button" data-variant-suggestion="size" data-variant-value="{{ $sizeSuggestion }}">{{ $sizeSuggestion }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Sugestões de cor</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($colorSuggestions as $colorSuggestion)
                                    <button class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-brand hover:text-brand" type="button" data-variant-suggestion="color" data-variant-value="{{ $colorSuggestion }}">{{ $colorSuggestion }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-line bg-slate-50 p-5">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                <div>
                    <h3 class="text-base font-bold text-ink">Imagens do produto</h3>
                    <p class="mt-1 text-sm text-slate-500">Envie várias imagens direto do computador. Formatos aceitos: JPG, PNG e WEBP, até 5MB por imagem.</p>
                </div>
                <span class="self-start rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500 shadow-soft whitespace-nowrap">Até 8 imagens</span>
            </div>

            <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-8 text-center transition hover:border-brand hover:bg-blue-50/50">
                <input id="imagesInput" class="hidden" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
                <span class="mb-2 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-brand">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 15l4-4a3 3 0 014.243 0L16 15m-2-2l1-1a3 3 0 014.243 0L21 13m-9-8h.01M6 19h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </span>
                <span class="text-sm font-semibold text-ink">Clique para selecionar imagens</span>
                <span class="mt-1 text-xs text-slate-500">As imagens são otimizadas automaticamente para carregar mais rápido.</span>
            </label>

            @if($existingImages->isNotEmpty())
                <div class="mt-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-ink">Imagens atuais</h4>
                        <span class="text-xs text-slate-500">Escolha qual será a capa ou marque para remover</span>
                    </div>
                    <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($existingImages as $existingImage)
                            @php
                                $isRemoved = $removedExistingImages->contains($existingImage);
                            @endphp
                            <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-soft transition" data-existing-image-card>
                                <img class="h-48 w-full object-cover" src="{{ $existingImage }}" alt="Imagem do produto {{ $loop->iteration }}">
                                <div class="space-y-3 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm font-semibold text-ink">Imagem {{ $loop->iteration }}</span>
                                        @if($product->image === $existingImage)
                                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-brand">Capa atual</span>
                                        @endif
                                    </div>

                                    <label class="block cursor-pointer">
                                        <input class="peer sr-only" type="radio" name="cover_choice" value="existing:{{ $existingImage }}" {{ $coverChoice === 'existing:'.$existingImage ? 'checked' : '' }}>
                                        <span class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand hover:bg-blue-50 hover:text-brand peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-soft">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span>Definir como capa</span>
                                        </span>
                                    </label>

                                    <label class="block cursor-pointer">
                                        <input class="peer sr-only" type="checkbox" data-remove-existing value="{{ $existingImage }}" name="remove_existing_images[]" {{ $isRemoved ? 'checked' : '' }}>
                                        <span class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100 peer-checked:border-red-600 peer-checked:bg-red-600 peer-checked:text-white">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" /></svg>
                                            <span>Apagar imagem</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-5">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-ink">Novas imagens</h4>
                    <span class="text-xs text-slate-500">Se nenhuma capa for escolhida, a primeira imagem disponível será usada.</span>
                </div>
                <div id="newImagesPreview" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
            </div>
        </div>

        <div>
            <label class="panel-label">Descrição</label>
            <div id="descEditorContainer" class="overflow-hidden rounded-2xl border border-line bg-white">
                <div id="descEditor" style="min-height:140px; font-size:14px;"></div>
            </div>
            <textarea class="hidden" name="description" id="descTextarea">{{ old('description', $product->description) }}</textarea>
        </div>

        <div>
            <label class="panel-label">Observações</label>
            <textarea class="panel-textarea" name="observations" rows="4" placeholder="Observações vindas do Bling ou informação complementar.">{{ old('observations', $product->observations) }}</textarea>
        </div>

        <div class="space-y-3">
            <div class="rounded-3xl border border-line bg-slate-50 p-5">
                <h3 class="text-base font-bold text-ink">Destaque na Home</h3>
                <p class="mt-1 text-sm text-slate-500">Escolha onde este produto deve aparecer: você pode marcar apenas 1 opção ou as 2.</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="block cursor-pointer">
                        <input class="peer sr-only" type="checkbox" name="highlight_best_sellers" value="1" {{ old('highlight_best_sellers', $product->highlight_best_sellers ?? false) ? 'checked' : '' }}>
                        <span class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand hover:bg-blue-50 hover:text-brand peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-soft">
                            <span>Mais Vendidos</span>
                        </span>
                    </label>

                    <label class="block cursor-pointer">
                        <input class="peer sr-only" type="checkbox" name="highlight_launches" value="1" {{ old('highlight_launches', $product->highlight_launches ?? false) ? 'checked' : '' }}>
                        <span class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand hover:bg-blue-50 hover:text-brand peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-soft">
                            <span>Lançamentos</span>
                        </span>
                    </label>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand" type="checkbox" name="active" value="1" {{ old('active', $product->active ?? true) ? 'checked' : '' }}>
                Produto ativo
            </label>
        </div>

        <div class="flex flex-wrap gap-2">
            <button class="panel-btn-primary" type="submit">Salvar</button>
            <a class="panel-btn-secondary" href="{{ route('admin.produtos.index') }}">Voltar</a>
        </div>
    </form>
</div>

<script>
    let pendingImageFiles = [];
    let sizesEditorApi = null;
    let colorsEditorApi = null;

    function createTagEditor(config) {
        const hiddenInput = document.getElementById(config.hiddenId);
        const tagsContainer = document.getElementById(config.tagsId);
        const editorInput = document.getElementById(config.editorId);
        const suggestionButtons = document.querySelectorAll(`[data-tag-suggestion="${config.name}"]`);

        if (!hiddenInput || !tagsContainer || !editorInput) {
            return null;
        }

        let items = (hiddenInput.value || '')
            .split(/[\n,]+/)
            .map(item => item.trim())
            .filter(Boolean);

        const sync = () => {
            hiddenInput.value = items.join("\n");
            tagsContainer.innerHTML = '';

            items.forEach((item, index) => {
                const tag = document.createElement('button');
                tag.type = 'button';
                tag.className = 'inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1.5 text-sm font-semibold text-blue-700';
                tag.innerHTML = `<span>${item}</span><span class="text-blue-500">×</span>`;
                tag.addEventListener('click', () => {
                    items.splice(index, 1);
                    sync();
                });
                tagsContainer.appendChild(tag);
            });
        };

        const addItem = value => {
            const normalized = value.trim();

            if (!normalized) {
                return;
            }

            const exists = items.some(item => item.toLowerCase() === normalized.toLowerCase());

            if (!exists) {
                items.push(normalized);
                sync();
            }
        };

        editorInput.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                addItem(editorInput.value);
                editorInput.value = '';
            }
        });

        editorInput.addEventListener('blur', () => {
            addItem(editorInput.value);
            editorInput.value = '';
        });

        suggestionButtons.forEach(button => {
            button.addEventListener('click', () => addItem(button.textContent || ''));
        });

        sync();

        return {
            getItems: () => [...items],
            setItems: (nextItems) => {
                items = (nextItems || [])
                    .map(item => String(item || '').trim())
                    .filter(Boolean)
                    .filter((item, index, array) => array.findIndex(candidate => candidate.toLowerCase() === item.toLowerCase()) === index);
                sync();
            },
            addItems: (nextItems) => {
                (nextItems || []).forEach(value => addItem(String(value || '')));
            }
        };
    }

    function collectVariantFieldValues(fieldName) {
        const rows = document.querySelectorAll('[data-variant-row]');

        return Array.from(rows)
            .map(row => {
                const input = row.querySelector(`input[name*="[${fieldName}]"]`);
                return (input?.value || '').trim();
            })
            .filter(Boolean)
            .filter((value, index, array) => array.findIndex(candidate => candidate.toLowerCase() === value.toLowerCase()) === index);
    }

    function syncTagEditorsFromVariants() {
        if (!sizesEditorApi || !colorsEditorApi) {
            return;
        }

        const variantSizes = collectVariantFieldValues('size');
        const variantColors = collectVariantFieldValues('color');

        sizesEditorApi.addItems(variantSizes);
        colorsEditorApi.addItems(variantColors);
    }

    function buildImagePreview(file, index, checked) {
        const wrapper = document.createElement('div');
        wrapper.className = 'overflow-hidden rounded-2xl border border-line bg-white shadow-soft';

        const objectUrl = URL.createObjectURL(file);

        wrapper.innerHTML = `
            <img class="h-48 w-full object-cover" src="${objectUrl}" alt="${file.name}">
            <div class="space-y-3 p-4">
                <div class="flex items-center justify-between gap-3">
                    <span class="truncate text-sm font-semibold text-slate-800">${file.name}</span>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Nova</span>
                </div>
                <label class="block cursor-pointer">
                    <input class="peer sr-only" type="radio" name="cover_choice" value="new:${index}" ${checked ? 'checked' : ''}>
                    <span class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand hover:bg-blue-50 hover:text-brand peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white peer-checked:shadow-soft">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Definir como capa</span>
                    </span>
                </label>
                <button class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100" type="button" data-remove-new-index="${index}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" /></svg>
                    <span>Apagar imagem</span>
                </button>
            </div>
        `;

        return wrapper;
    }

    function syncPendingImagesInput() {
        const input = document.getElementById('imagesInput');
        const dataTransfer = new DataTransfer();

        pendingImageFiles.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }

    function refreshNewImagesPreview() {
        const preview = document.getElementById('newImagesPreview');
        const files = pendingImageFiles;
        const hasCoverSelected = !!document.querySelector('input[name="cover_choice"]:checked');

        preview.innerHTML = '';

        files.forEach((file, index) => {
            const card = buildImagePreview(file, index, !hasCoverSelected && index === 0);
            preview.appendChild(card);
        });
    }

    function removeNewImage(indexToRemove) {
        pendingImageFiles = pendingImageFiles.filter((file, index) => index !== indexToRemove);
        syncPendingImagesInput();
        refreshNewImagesPreview();

        const checkedCover = document.querySelector('input[name="cover_choice"]:checked');
        if (!checkedCover) {
            const fallback = document.querySelector('input[name="cover_choice"]');
            if (fallback) {
                fallback.checked = true;
            }
        }
    }

    function syncCoverAfterRemoval() {
        const removeCheckboxes = document.querySelectorAll('[data-remove-existing]');

        removeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const options = Array.from(document.querySelectorAll('input[name="cover_choice"]'));
                const current = options.find(option => option.value === `existing:${checkbox.value}`);

                if (checkbox.checked && current && current.checked) {
                    const fallback = options.find(option => option !== current);
                    if (fallback) {
                        fallback.checked = true;
                    }
                }
            });
        });
    }

    function reindexVariantRows() {
        const rows = document.querySelectorAll('[data-variant-row]');

        rows.forEach((row, index) => {
            const sizeInput = row.querySelector('input[name*="[size]"]');
            const colorInput = row.querySelector('input[name*="[color]"]');
            const stockInput = row.querySelector('input[name*="[stock]"]');

            if (sizeInput) {
                sizeInput.name = `variant_stocks[${index}][size]`;
            }

            if (colorInput) {
                colorInput.name = `variant_stocks[${index}][color]`;
            }

            if (stockInput) {
                stockInput.name = `variant_stocks[${index}][stock]`;
            }
        });
    }

    function addVariantRow() {
        const container = document.getElementById('variantRows');
        if (!container) {
            return;
        }

        const index = container.querySelectorAll('[data-variant-row]').length;
        const row = document.createElement('div');
        row.className = 'grid gap-2 sm:grid-cols-[1fr_1fr_120px_auto]';
        row.setAttribute('data-variant-row', '');
        row.innerHTML = `
            <input class="panel-input" type="text" name="variant_stocks[${index}][size]" placeholder="Tamanho (ex: P)">
            <input class="panel-input" type="text" name="variant_stocks[${index}][color]" placeholder="Cor (ex: Preto)">
            <div data-variant-stock-col>
                <input class="panel-input" type="number" min="0" max="99999" name="variant_stocks[${index}][stock]" placeholder="Qtd" value="0">
            </div>
            <button class="panel-btn-secondary" type="button" data-remove-variant>Linha</button>
        `;

        container.appendChild(row);
        reindexVariantRows();

        const selected = document.querySelector('input[name="track_stock"]:checked');
        const isLimited = selected && selected.value === '1';
        applyVariantStockMode(isLimited);
    }

    function applyVariantStockMode(isLimited) {
        const rows = Array.from(document.querySelectorAll('[data-variant-row]'));

        rows.forEach(row => {
            row.className = isLimited
                ? 'grid gap-2 sm:grid-cols-[1fr_1fr_120px_auto]'
                : 'grid gap-2 sm:grid-cols-[1fr_1fr_auto]';

            const stockCol = row.querySelector('[data-variant-stock-col]');
            const stockInput = row.querySelector('input[name*="[stock]"]');

            if (stockCol) {
                stockCol.classList.toggle('hidden', !isLimited);
            }

            if (stockInput) {
                stockInput.disabled = !isLimited;
            }
        });
    }

    function applyVariantSuggestion(fieldName, value) {
        const variantRows = document.getElementById('variantRows');
        if (!variantRows) {
            return;
        }

        const allInputs = Array.from(variantRows.querySelectorAll(`input[name*="[${fieldName}]"]`));
        if (!allInputs.length) {
            return;
        }

        const activeElement = document.activeElement;
        const isMatchingActiveInput = activeElement instanceof HTMLInputElement
            && activeElement.name.includes(`[${fieldName}]`)
            && variantRows.contains(activeElement);

        const targetInput = isMatchingActiveInput
            ? activeElement
            : (allInputs.find(input => String(input.value || '').trim() === '') || allInputs[0]);

        targetInput.value = value;
        targetInput.dispatchEvent(new Event('input', { bubbles: true }));
        targetInput.focus();
    }

    document.addEventListener('DOMContentLoaded', () => {
        sizesEditorApi = createTagEditor({ name: 'sizes', hiddenId: 'sizesHidden', tagsId: 'sizesTags', editorId: 'sizesEditor' });
        colorsEditorApi = createTagEditor({ name: 'colors', hiddenId: 'colorsHidden', tagsId: 'colorsTags', editorId: 'colorsEditor' });

        const imagesInput = document.getElementById('imagesInput');
        if (imagesInput) {
            imagesInput.addEventListener('change', event => {
                const selectedFiles = Array.from(event.target.files || []);

                selectedFiles.forEach(file => {
                    const exists = pendingImageFiles.some(existingFile => (
                        existingFile.name === file.name
                        && existingFile.size === file.size
                        && existingFile.lastModified === file.lastModified
                    ));

                    if (!exists && pendingImageFiles.length < 8) {
                        pendingImageFiles.push(file);
                    }
                });

                syncPendingImagesInput();
                refreshNewImagesPreview();
            });
        }

        const newImagesPreview = document.getElementById('newImagesPreview');
        if (newImagesPreview) {
            newImagesPreview.addEventListener('click', event => {
                const button = event.target.closest('[data-remove-new-index]');
                if (!button) {
                    return;
                }

                removeNewImage(Number(button.dataset.removeNewIndex));
            });
        }

        syncCoverAfterRemoval();

        const stockRadios = document.querySelectorAll('input[name="track_stock"]');
        const stockField = document.getElementById('stockLimitedField');
        const stockSingleField = document.getElementById('stockSingleField');
        const variantStockPanel = document.getElementById('variantStockPanel');
        const variantRows = document.getElementById('variantRows');
        const addVariantRowBtn = document.getElementById('addVariantRow');

        if (addVariantRowBtn) {
            addVariantRowBtn.addEventListener('click', addVariantRow);
        }

        if (variantRows) {
            const updateSizesColorsVisibility = () => {
                const hasFilledVariants = Array.from(variantRows.querySelectorAll('[data-variant-row]')).some(row => {
                    const size = row.querySelector('input[name*="[size]"]')?.value.trim() || '';
                    const color = row.querySelector('input[name*="[color]"]')?.value.trim() || '';
                    const stock = row.querySelector('input[name*="[stock]"]')?.value || '0';
                    return size || color || (Number(stock) > 0);
                });

                const sizesColorsSection = document.querySelector('.grid.gap-6.lg\\:grid-cols-2');
                if (sizesColorsSection) {
                    const isVariantModeActive = variantStockPanel && !variantStockPanel.classList.contains('hidden');
                    sizesColorsSection.classList.toggle('hidden', isVariantModeActive && hasFilledVariants);
                }
            };

            variantRows.addEventListener('input', event => {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) {
                    return;
                }

                if (target.name.includes('[size]') || target.name.includes('[color]') || target.name.includes('[stock]')) {
                    syncTagEditorsFromVariants();
                    updateSizesColorsVisibility();
                }
            });

            variantRows.addEventListener('click', event => {
                const removeButton = event.target.closest('[data-remove-variant]');
                if (!removeButton) {
                    return;
                }

                const row = removeButton.closest('[data-variant-row]');
                if (!row) {
                    return;
                }

                const allRows = variantRows.querySelectorAll('[data-variant-row]');
                if (allRows.length <= 1) {
                    row.querySelectorAll('input').forEach(input => {
                        if (input.type === 'number') {
                            input.value = '0';
                        } else {
                            input.value = '';
                        }
                    });
                    updateSizesColorsVisibility();
                    return;
                }

                row.remove();
                reindexVariantRows();
                syncTagEditorsFromVariants();
                updateSizesColorsVisibility();
            });

            updateSizesColorsVisibility();
        }

        document.addEventListener('click', event => {
            const suggestionBtn = event.target.closest('[data-variant-suggestion]');
            if (!suggestionBtn) {
                return;
            }

            const fieldName = suggestionBtn.getAttribute('data-variant-suggestion');
            const value = suggestionBtn.getAttribute('data-variant-value') || '';
            if (!fieldName || !value) {
                return;
            }

            applyVariantSuggestion(fieldName, value);
        });

        const syncStockMode = () => {
            const selected = document.querySelector('input[name="track_stock"]:checked');
            const isLimited = selected && selected.value === '1';

            stockField.classList.remove('hidden');
            const stockInput = stockField.querySelector('input[name="stock"]');
            stockInput.required = false;

            if (stockSingleField) {
                stockSingleField.classList.add('hidden');
            }

            if (variantStockPanel) {
                variantStockPanel.classList.remove('hidden');
            }

            applyVariantStockMode(isLimited);
        };

        stockRadios.forEach(radio => radio.addEventListener('change', syncStockMode));
        syncStockMode();
        syncTagEditorsFromVariants();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
(function () {
    const descTextarea = document.getElementById('descTextarea');
    const quill = new Quill('#descEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [false, 2, 3] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean']
            ]
        },
        placeholder: 'Descreva o produto...'
    });

    // Carrega conteúdo existente
    const initialContent = descTextarea ? descTextarea.value.trim() : '';
    if (initialContent) {
        if (initialContent.startsWith('<')) {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
        } else {
            quill.setText(initialContent);
        }
    }

    // Ao submeter o formulário, sincroniza o HTML para o textarea oculto
    const productForm = descTextarea ? descTextarea.closest('form') : null;
    if (productForm) {
        productForm.addEventListener('submit', function () {
            descTextarea.value = quill.getSemanticHTML();
        });
    }

    // Estilo customizado para remover borda dupla e harmonizar com o painel
    const editorEl = document.getElementById('descEditorContainer');
    if (editorEl) {
        const qlContainer = editorEl.querySelector('.ql-container');
        if (qlContainer) qlContainer.style.borderBottom = 'none';
        const qlToolbar = editorEl.querySelector('.ql-toolbar');
        if (qlToolbar) { qlToolbar.style.borderTop = 'none'; qlToolbar.style.borderLeft = 'none'; qlToolbar.style.borderRight = 'none'; }
    }
})();
</script>
@endsection
