@extends('admin.layout')

@section('title', 'Importar produtos Bling')
@section('heading', 'Importar produtos Bling')

@section('content')
<div class="grid gap-4 lg:grid-cols-[1fr_320px]">
    <div class="space-y-4">
        <div class="panel-card panel-card-body">
            <form method="GET" action="{{ route('admin.bling.products.index') }}" class="grid gap-3 md:grid-cols-[1fr_auto] md:items-end">
                <label>
                    <span class="panel-label">Buscar no Bling</span>
                    <input class="panel-input" type="text" name="q" value="{{ $search }}" placeholder="Nome, codigo ou termo do produto">
                </label>
                <button class="panel-btn-primary" type="submit">Buscar produtos</button>
            </form>

            @unless($blingConfigured)
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                    Conecte sua conta Bling para habilitar a busca real de produtos.
                    <a class="underline" href="{{ route('admin.bling.auth') }}">Abrir conexao Bling</a>
                </div>
            @endunless

            @if($error)
                <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    {{ $error }}
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.bling.products.import') }}" id="blingImportForm" class="panel-card overflow-hidden">
            @csrf
            <input type="hidden" name="q" value="{{ $search }}">

            <div class="border-b border-line bg-cloud px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <label class="min-w-0 flex-1">
                        <span class="panel-label">Categoria de destino</span>
                        <select class="panel-select" name="category_id" required>
                            <option value="">Selecione a categoria para os produtos marcados</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex flex-wrap gap-2">
                        <label class="panel-btn-secondary cursor-pointer">
                            <input class="mr-2 h-4 w-4" type="checkbox" name="update_existing" value="1" checked>
                            Atualizar existentes
                        </label>
                        <label class="panel-btn-secondary cursor-pointer">
                            <input class="mr-2 h-4 w-4" type="checkbox" name="activate_products" value="1" checked>
                            Ativar produtos
                        </label>
                    </div>
                </div>

                @if(! empty($results))
                    @php
                        $newCount = collect($results)->reject(fn ($item) => isset($imported[$item['bling_id']]))->count();
                        $importedCount = count($results) - $newCount;
                    @endphp
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-muted">
                        <span class="rounded-full border border-line bg-white px-3 py-1">{{ $newCount }} novo(s) primeiro</span>
                        <span class="rounded-full border border-line bg-white px-3 py-1">{{ $importedCount }} ja importado(s)</span>
                    </div>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <span class="text-xs font-bold uppercase tracking-wide text-muted">Exibir</span>
                        <div class="flex flex-wrap gap-2" data-import-filter>
                            <button class="rounded-lg bg-brand px-3 py-2 text-xs font-bold text-white" type="button" data-filter-value="all">Todos</button>
                            <button class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-muted hover:bg-slate-50" type="button" data-filter-value="new">Nao importados</button>
                            <button class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-muted hover:bg-slate-50" type="button" data-filter-value="imported">Ja importados</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="panel-table-wrap">
                <table class="panel-table">
                    <thead class="panel-thead">
                    <tr>
                        <th class="panel-th w-12">
                            <input class="h-4 w-4 rounded border-slate-300" type="checkbox" data-select-all>
                        </th>
                        <th class="panel-th">Produto</th>
                        <th class="panel-th hidden md:table-cell">Codigo</th>
                        <th class="panel-th">Preco</th>
                        <th class="panel-th hidden sm:table-cell">Estoque</th>
                        <th class="panel-th hidden xl:table-cell">Medidas</th>
                        <th class="panel-th hidden lg:table-cell">Status</th>
                    </tr>
                    </thead>
                    <tbody class="panel-table-body" data-bling-table-body>
                    @forelse($results as $item)
                        @php($alreadyImported = isset($imported[$item['bling_id']]))
                        <tr data-product-row data-imported="{{ $alreadyImported ? '1' : '0' }}">
                            <td class="panel-td">
                                <input class="h-4 w-4 rounded border-slate-300" type="checkbox" name="bling_ids[]" value="{{ $item['bling_id'] }}" data-product-checkbox>
                            </td>
                            <td class="panel-td-strong">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-line bg-slate-50">
                                        @if($item['image'])
                                            <img class="h-full w-full object-cover" src="{{ $item['image'] }}" alt="">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block truncate">{{ $item['name'] }}</span>
                                        <span class="block text-xs text-muted font-normal md:hidden">{{ $item['code'] ?: 'Sem codigo' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="panel-td hidden md:table-cell">{{ $item['code'] ?: '-' }}</td>
                            <td class="panel-td">R$ {{ number_format((float) $item['price'], 2, ',', '.') }}</td>
                            <td class="panel-td hidden sm:table-cell">{{ $item['stock'] }}</td>
                            <td class="panel-td hidden xl:table-cell">
                                <span class="block text-xs">Liq: {{ $item['weight_grams'] ?: 300 }}g</span>
                                <span class="block text-xs">Bruto: {{ $item['gross_weight_grams'] ?: '-' }}g</span>
                                <span class="block text-xs">{{ $item['width_cm'] ?: '-' }} x {{ $item['height_cm'] ?: '-' }} x {{ $item['depth_cm'] ?: '-' }} cm</span>
                            </td>
                            <td class="panel-td hidden lg:table-cell">
                                <span class="{{ $alreadyImported ? 'panel-badge-green' : 'panel-badge-gray' }}">
                                    {{ $alreadyImported ? 'Ja importado' : 'Novo' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="panel-td py-8 text-center text-slate-500">
                                @if($search !== '')
                                    Nenhum produto encontrado para "{{ $search }}".
                                @else
                                    Busque um termo para listar produtos do Bling.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-line px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-muted"><span data-selected-count>0</span> produto(s) selecionado(s)</p>
                    <p class="text-xs font-semibold text-slate-400" data-page-summary></p>
                </div>
                <button class="panel-btn-primary" type="submit" {{ empty($results) ? 'disabled' : '' }} data-import-button>
                    Importar selecionados
                </button>
            </div>

            @if(count($results) > 20)
                <div class="flex flex-col gap-3 border-t border-line bg-cloud px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5" data-bling-pagination>
                    <p class="text-sm font-semibold text-muted" data-pagination-label></p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="panel-btn-secondary px-3 py-2 text-xs" type="button" data-page-prev>Anterior</button>
                        <div class="flex flex-wrap gap-1" data-page-buttons></div>
                        <button class="panel-btn-secondary px-3 py-2 text-xs" type="button" data-page-next>Proxima</button>
                    </div>
                </div>
            @endif
        </form>
    </div>

    <aside class="panel-card panel-card-body self-start">
        <h2 class="panel-section-title">Fluxo recomendado</h2>
        <div class="mt-4 space-y-3 text-sm text-slate-600">
            <p><strong>1.</strong> Busque os produtos no Bling.</p>
            <p><strong>2.</strong> Marque somente o que voce quer vender na loja.</p>
            <p><strong>3.</strong> Escolha a categoria e importe em lote.</p>
            <p><strong>4.</strong> Depois ajuste imagens, destaque e descricao se quiser refinar a vitrine.</p>
        </div>
        <div class="mt-5 rounded-2xl border border-line bg-cloud px-4 py-3 text-xs font-semibold text-slate-500">
            Produtos importados ficam vinculados pelo ID do Bling, entao futuras importacoes podem atualizar o item certo.
        </div>
    </aside>
</div>

@push('scripts')
<script>
(() => {
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    const checkboxes = Array.from(document.querySelectorAll('[data-product-checkbox]'));
    const selectAll = document.querySelector('[data-select-all]');
    const count = document.querySelector('[data-selected-count]');
    const importButton = document.querySelector('[data-import-button]');
    const pageSummary = document.querySelector('[data-page-summary]');
    const pagination = document.querySelector('[data-bling-pagination]');
    const paginationLabel = document.querySelector('[data-pagination-label]');
    const pageButtons = document.querySelector('[data-page-buttons]');
    const prevButton = document.querySelector('[data-page-prev]');
    const nextButton = document.querySelector('[data-page-next]');
    const filterButtons = Array.from(document.querySelectorAll('[data-filter-value]'));

    const pageSize = 20;
    let currentPage = 1;
    let currentFilter = 'all';

    function filteredRows() {
        return rows.filter(row => {
            if (currentFilter === 'new') return row.dataset.imported === '0';
            if (currentFilter === 'imported') return row.dataset.imported === '1';
            return true;
        });
    }

    function totalPages() {
        return Math.max(1, Math.ceil(filteredRows().length / pageSize));
    }

    function visibleRows() {
        return rows.filter(row => row.style.display !== 'none');
    }

    function renderPaginationButtons() {
        if (!pageButtons) return;

        pageButtons.innerHTML = '';
        for (let page = 1; page <= totalPages(); page++) {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = page;
            button.className = page === currentPage
                ? 'rounded-lg bg-brand px-3 py-2 text-xs font-bold text-white'
                : 'rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-muted hover:bg-slate-50';
            button.addEventListener('click', () => {
                currentPage = page;
                renderPage();
            });
            pageButtons.appendChild(button);
        }
    }

    function refreshSelection() {
        const selected = checkboxes.filter(checkbox => checkbox.checked).length;
        const visibleCheckboxes = visibleRows()
            .map(row => row.querySelector('[data-product-checkbox]'))
            .filter(Boolean);
        const visibleSelected = visibleCheckboxes.filter(checkbox => checkbox.checked).length;

        if (count) count.textContent = selected;
        if (importButton) importButton.disabled = selected === 0;
        if (selectAll) {
            selectAll.checked = visibleCheckboxes.length > 0 && visibleSelected === visibleCheckboxes.length;
            selectAll.indeterminate = visibleSelected > 0 && visibleSelected < visibleCheckboxes.length;
        }
    }

    function renderPage() {
        const currentRows = filteredRows();
        const pages = totalPages();
        currentPage = Math.min(currentPage, pages);

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;

        rows.forEach(row => row.style.display = 'none');
        currentRows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? '' : 'none';
        });

        if (pageSummary) {
            pageSummary.textContent = currentRows.length
                ? 'Mostrando ' + (start + 1) + '-' + Math.min(end, currentRows.length) + ' de ' + currentRows.length + ' produto(s).'
                : 'Nenhum produto neste filtro.';
        }

        if (paginationLabel) {
            paginationLabel.textContent = 'Pagina ' + currentPage + ' de ' + pages;
        }

        if (prevButton) prevButton.disabled = currentPage === 1;
        if (nextButton) nextButton.disabled = currentPage === pages;

        renderPaginationButtons();
        refreshSelection();
    }

    checkboxes.forEach(checkbox => checkbox.addEventListener('change', refreshSelection));

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            visibleRows().forEach(row => {
                const checkbox = row.querySelector('[data-product-checkbox]');
                if (checkbox) checkbox.checked = selectAll.checked;
            });
            refreshSelection();
        });
    }

    prevButton?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderPage();
        }
    });

    nextButton?.addEventListener('click', () => {
        if (currentPage < totalPages()) {
            currentPage++;
            renderPage();
        }
    });

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            currentFilter = button.dataset.filterValue || 'all';
            currentPage = 1;

            filterButtons.forEach(item => {
                const active = item === button;
                item.className = active
                    ? 'rounded-lg bg-brand px-3 py-2 text-xs font-bold text-white'
                    : 'rounded-lg border border-line bg-white px-3 py-2 text-xs font-bold text-muted hover:bg-slate-50';
            });

            renderPage();
        });
    });

    document.getElementById('blingImportForm')?.addEventListener('submit', () => {
        if (importButton) {
            importButton.disabled = true;
            importButton.textContent = 'Importando...';
        }
    });

    if (pagination && totalPages() <= 1) {
        pagination.hidden = true;
    }

    renderPage();
})();
</script>
@endpush
@endsection
