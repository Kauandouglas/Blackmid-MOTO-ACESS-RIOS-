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
                    <tbody class="panel-table-body">
                    @forelse($results as $item)
                        @php($alreadyImported = isset($imported[$item['bling_id']]))
                        <tr>
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
                <p class="text-sm font-semibold text-muted"><span data-selected-count>0</span> produto(s) selecionado(s)</p>
                <button class="panel-btn-primary" type="submit" {{ empty($results) ? 'disabled' : '' }} data-import-button>
                    Importar selecionados
                </button>
            </div>
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
const checkboxes = Array.from(document.querySelectorAll('[data-product-checkbox]'));
const selectAll = document.querySelector('[data-select-all]');
const count = document.querySelector('[data-selected-count]');
const importButton = document.querySelector('[data-import-button]');

function refreshSelection() {
    const selected = checkboxes.filter(checkbox => checkbox.checked).length;
    if (count) count.textContent = selected;
    if (importButton) importButton.disabled = selected === 0;
    if (selectAll) {
        selectAll.checked = selected > 0 && selected === checkboxes.length;
        selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
    }
}

checkboxes.forEach(checkbox => checkbox.addEventListener('change', refreshSelection));

if (selectAll) {
    selectAll.addEventListener('change', () => {
        checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
        refreshSelection();
    });
}

document.getElementById('blingImportForm')?.addEventListener('submit', () => {
    if (importButton) {
        importButton.disabled = true;
        importButton.textContent = 'Importando...';
    }
});

refreshSelection();
</script>
@endpush
@endsection
