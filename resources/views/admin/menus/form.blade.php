@extends('admin.layout')

@section('title', $menu->exists ? 'Editar menu' : 'Novo menu')
@section('heading', $menu->exists ? 'Editar menu' : 'Novo menu')

@section('content')
<script>
    function setItemType(type) {
        if (type === 'main') {
            document.getElementById('typeMain').checked = true;
        } else {
            document.getElementById('typeSub').checked = true;
        }
        toggleParentSelection();
    }

    function toggleParentSelection() {
        const isSub = document.getElementById('typeSub').checked;
        const parentSection = document.getElementById('parentSelection');
        const parentSelect = document.getElementById('parentSelect');
        
        if (isSub) {
            parentSection.classList.remove('hidden');
            parentSelect.required = true;
        } else {
            parentSection.classList.add('hidden');
            parentSelect.required = false;
            parentSelect.value = '';
        }
    }

    function toggleLinkOptions() {
        const type = document.getElementById('linkType').value;
        document.getElementById('pageOption').classList.toggle('hidden', type !== 'page');
        document.getElementById('customOption').classList.toggle('hidden', type !== 'custom');
        
        // Prepara o campo URL antes de enviar
        const form = document.querySelector('#newItemSection form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const linkType = document.getElementById('linkType').value;
                const urlField = document.getElementById('urlField');
                
                if (linkType === 'page') {
                    const categoryId = document.getElementById('pageSelect').value;
                    urlField.value = categoryId || '';
                } else if (linkType === 'blog') {
                    urlField.value = 'blog';
                } else if (linkType === 'custom') {
                    urlField.value = document.getElementById('customUrlInput').value;
                }
            }, { once: true });
        }
    }

    function toggleLinkOptionsEdit() {
        const type = document.getElementById('linkTypeEdit').value;
        document.getElementById('pageOptionEdit').classList.toggle('hidden', type !== 'page');
        document.getElementById('customOptionEdit').classList.toggle('hidden', type !== 'custom');
        
        // Prepara o campo URL antes de enviar
        const form = document.querySelector('#menuItemForm form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const linkType = document.getElementById('linkTypeEdit').value;
                const urlField = document.getElementById('urlFieldEdit');
                
                if (linkType === 'page') {
                    const categoryId = document.getElementById('pageSelectEdit').value;
                    urlField.value = categoryId || '';
                } else if (linkType === 'blog') {
                    urlField.value = 'blog';
                } else if (linkType === 'custom') {
                    urlField.value = document.getElementById('customUrlInputEdit').value;
                }
            }, { once: true });
        }
    }
</script>
@if($menu->exists)
<!-- Menu Items Layout -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
    <!-- Left Panel: Menu Items Tree -->
    <div class="lg:col-span-1">
        <div class="overflow-hidden rounded-lg border border-gray-300 bg-white shadow-soft">
            <div class="border-b border-gray-300 px-5 py-4 bg-slate-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800">Estrutura do Menu</h3>
                <button type="button" class="p-1 text-blue-600 hover:text-blue-700 transition" title="Criar novo item" onclick="document.getElementById('newItemSection').classList.remove('hidden')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
            <div class="max-h-[600px] overflow-y-auto">
                @if($menuItems->isNotEmpty())
                    <ul class="divide-y divide-line bg-white menu-item-list" data-parent-id="">
                        @php
                            function renderMenuItem($item, $level = 0) {
                                $isActive = request()->get('item_id') == $item->id;
                                $hasChildren = $item->children->isNotEmpty();
                                $indent = $level * 16;
                                
                                $bgClass = $isActive ? 'bg-blue-50' : 'hover:bg-slate-50';
                                $textClass = $isActive ? 'text-blue-700 font-semibold' : 'text-slate-700';
                                $borderClass = $isActive ? 'border-l-4 border-blue-600' : '';
                                
                                echo '<li class="menu-item-node" data-item-id="' . $item->id . '">';
                                echo '<a href="' . route('admin.menus.edit', ['menu' => $item->menu_id, 'item_id' => $item->id]) . '#menuItemForm" class="flex items-center gap-3 px-5 py-3 transition ' . $bgClass . ' ' . $borderClass . '">';
                                echo '<span class="drag-handle inline-flex items-center justify-center rounded border border-slate-200 bg-white p-1 text-slate-400 hover:text-slate-700 cursor-grab active:cursor-grabbing" title="Arrastar para ordenar" aria-label="Arrastar para ordenar">';
                                echo '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M9 5h.01M9 12h.01M9 19h.01M15 5h.01M15 12h.01M15 19h.01"/></svg>';
                                echo '</span>';
                                
                                // Ícone para menu principal vs submenu
                                if($level === 0) {
                                    echo '<svg class="w-4 h-4 flex-shrink-0 ' . ($isActive ? 'text-blue-600' : 'text-slate-400') . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg>';
                                } else {
                                    echo '<svg class="w-4 h-4 flex-shrink-0 ' . ($isActive ? 'text-blue-600' : 'text-slate-400') . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>';
                                }
                                
                                echo '<span style="margin-left: ' . $indent . 'px" class="' . $textClass . ' text-sm">' . $item->title;
                                if($hasChildren) {
                                    echo ' <span class="text-xs text-slate-500 font-normal">(' . count($item->children) . ')</span>';
                                }
                                echo '</span>';
                                echo '</a>';
                                
                                if($hasChildren) {
                                    echo '<ul class="bg-slate-50 divide-y divide-line menu-item-list" data-parent-id="' . $item->id . '">';
                                    foreach($item->children as $child) {
                                        renderMenuItem($child, $level + 1);
                                    }
                                    echo '</ul>';
                                }
                                echo '</li>';
                            }
                            foreach($menuItems->where('parent_id', null) as $item) {
                                renderMenuItem($item);
                            }
                        @endphp
                    </ul>
                @else
                    <div class="px-5 py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path></svg>
                        <p class="text-slate-500 text-sm">Nenhum item de menu</p>
                    </div>
                @endif
            </div>
            <div class="border-t border-gray-300 px-5 py-3 bg-slate-50">
                <div class="flex items-center justify-between gap-3">
                <button type="button" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-2 transition" onclick="document.getElementById('newItemSection').classList.toggle('hidden')">
                    <span class="w-5 h-5 flex items-center justify-center bg-blue-600 text-white rounded text-xs font-bold">+</span>
                    <span>Adicionar item ao menu</span>
                </button>
                <button id="saveOrderBtn" type="button" class="hidden text-xs bg-slate-800 hover:bg-black text-white px-3 py-1.5 rounded transition">
                    Salvar ordem
                </button>
                </div>
                <p class="mt-2 text-[11px] text-slate-500">Dica: arraste os itens pelo ícone de pontos para reordenar.</p>
            </div>
        </div>
    </div>

    <!-- Right Panel: Edit Form -->
    <div class="lg:col-span-3">
        <!-- New Item Form -->
        <div id="newItemSection" class="panel-card panel-card-body hidden mb-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Novo item de menu</h3>
            </div>
            <form method="POST" action="{{ route('admin.menu-items.store', $menu) }}" class="space-y-4">
                @csrf
                
                <!-- Escolher tipo de item -->
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-3">Tipo de item</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-600 hover:bg-blue-50 transition" onclick="setItemType('main')">
                            <input type="radio" name="item_type" value="main" id="typeMain" class="w-4 h-4" checked onchange="toggleParentSelection()">
                            <div>
                                <span class="block text-sm font-semibold text-slate-800">📄 Menu Principal</span>
                                <span class="text-xs text-slate-500">Aparece no menu top</span>
                            </div>
                        </label>
                        <label class="relative flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-600 hover:bg-blue-50 transition" onclick="setItemType('sub')">
                            <input type="radio" name="item_type" value="sub" id="typeSub" class="w-4 h-4" onchange="toggleParentSelection()">
                            <div>
                                <span class="block text-sm font-semibold text-slate-800">➡️ Submenu</span>
                                <span class="text-xs text-slate-500">Fica dentro de outro</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Selecionar menu pai se for submenu -->
                <div id="parentSelection" class="hidden">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Submenu de qual item?</label>
                    <select class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="parent_id" id="parentSelect">
                        <option value="">Selecione um menu...</option>
                        @foreach($menuItems->where('parent_id', null) as $item)
                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Nome do item *</label>
                    <input class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" type="text" name="title" placeholder="Ex: Produtos, Contato, Sobre..." required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Leva a</label>
                    <select class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="link_type" id="linkType" onchange="toggleLinkOptions()">
                        <option value="page">📄 Página</option>
                        <option value="blog">📝 Blog</option>
                        <option value="custom">🔗 URL Personalizada</option>
                    </select>
                </div>

                <!-- Opção de Página -->
                <div id="pageOption" class="block">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Selecione a página</label>
                    <select class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="category_id" id="pageSelect">
                        <option value="">Página inicial</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Opção de URL Personalizada -->
                <div id="customOption" class="hidden">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Digite a URL</label>
                    <input class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" type="text" name="custom_url" id="customUrlInput" placeholder="https://exemplo.com ou /pagina">
                </div>

                <!-- Campo hidden para armazenar a URL final -->
                <input type="hidden" name="url" id="urlField" value="">

                <div class="flex gap-3 pt-4">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg text-sm font-semibold transition shadow-soft" type="submit">Salvar</button>
                    <button class="border border-gray-300 hover:bg-slate-50 text-slate-700 px-8 py-2.5 rounded-lg text-sm font-semibold transition" type="button" onclick="document.getElementById('newItemSection').classList.add('hidden')">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Edit Item Form -->
        @php
            $selectedItem = request()->get('item_id') ? $menuItems->firstWhere('id', request()->get('item_id')) : null;
        @endphp
        @if($selectedItem)
            <div id="menuItemForm" class="panel-card panel-card-body">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-300">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-800">{{ $selectedItem->title }}</h3>
                        <p class="text-xs text-slate-500">Item de submenu</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.menu-items.update', $selectedItem) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Nome do item *</label>
                        <input class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" type="text" name="title" value="{{ $selectedItem->title }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Leva a</label>
                        <select class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="link_type" id="linkTypeEdit" onchange="toggleLinkOptionsEdit()">
                            @php
                                $linkType = 'page';
                                if($selectedItem->url === 'blog') $linkType = 'blog';
                                elseif($selectedItem->url && $selectedItem->url !== 'blog') $linkType = 'custom';
                            @endphp
                            <option value="page" {{ $linkType === 'page' ? 'selected' : '' }}>📄 Página</option>
                            <option value="blog" {{ $linkType === 'blog' ? 'selected' : '' }}>📝 Blog</option>
                            <option value="custom" {{ $linkType === 'custom' ? 'selected' : '' }}>🔗 URL Personalizada</option>
                        </select>
                    </div>

                    <!-- Opção de Página -->
                    <div id="pageOptionEdit" class="{{ $linkType === 'page' ? 'block' : 'hidden' }}">
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Selecione a página</label>
                        <select class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="category_id" id="pageSelectEdit">
                            <option value="">Página inicial</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $selectedItem->category_id === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Opção de URL Personalizada -->
                    <div id="customOptionEdit" class="{{ $linkType === 'custom' ? 'block' : 'hidden' }}">
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Digite a URL</label>
                        <input class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" type="text" name="custom_url" id="customUrlInputEdit" placeholder="https://exemplo.com ou /pagina" value="{{ $linkType === 'custom' ? $selectedItem->url : '' }}">
                    </div>

                    <!-- Campo hidden para armazenar a URL final -->
                    <input type="hidden" name="url" id="urlFieldEdit" value="{{ $selectedItem->url }}">

                    <div class="flex gap-3 pt-6 border-t border-gray-300">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg text-sm font-semibold transition shadow-soft" type="submit">Salvar</button>
                        <button class="border border-gray-300 hover:bg-slate-50 text-slate-700 px-8 py-2.5 rounded-lg text-sm font-semibold transition" type="button" onclick="history.back()">Cancelar</button>
                    </div>
                </form>
                <form class="mt-3 flex justify-end" method="POST" action="{{ route('admin.menu-items.destroy', $selectedItem) }}" onsubmit="return confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.');">
                    @csrf
                    @method('DELETE')
                    <button class="bg-red-600 hover:bg-red-700 text-white px-8 py-2.5 rounded-lg text-sm font-semibold transition shadow-soft" type="submit">🗑️ Excluir</button>
                </form>
            </div>
        @else
            <div class="panel-card panel-card-body text-center py-16">
                <div class="w-16 h-16 bg-slate-100 rounded-lg mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-slate-600 text-sm font-medium mb-2">Nenhum item selecionado</p>
                <p class="text-slate-500 text-xs mb-4">Selecione um item do menu à esquerda para editar</p>
                <button type="button" class="text-blue-600 hover:text-blue-700 font-medium text-sm inline-flex items-center gap-2 transition" onclick="document.getElementById('newItemSection').classList.remove('hidden')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Ou crie um novo item
                </button>
            </div>
        @endif
    </div>
</div>
    <script>
        (function () {
            const reorderUrl = '{{ route('admin.menu-items.reorder', $menu) }}';
            const csrfToken = '{{ csrf_token() }}';
            const saveBtn = document.getElementById('saveOrderBtn');
            const itemNodes = Array.from(document.querySelectorAll('.menu-item-node'));

            if (!itemNodes.length || !saveBtn) return;

            let dragNode = null;
            let dirty = false;

            function parentListOf(node) {
                return node.closest('.menu-item-list');
            }

            function markDirty() {
                dirty = true;
                saveBtn.classList.remove('hidden');
            }

            function cleanDragStyles() {
                document.querySelectorAll('.menu-item-node').forEach((n) => {
                    n.classList.remove('opacity-60');
                    n.classList.remove('ring-1', 'ring-blue-300');
                    n.removeAttribute('draggable');
                });
            }

            function collectGroups() {
                const groups = [];
                document.querySelectorAll('.menu-item-list').forEach((list) => {
                    const ids = Array.from(list.children)
                        .map((li) => Number(li.dataset.itemId))
                        .filter((id) => Number.isFinite(id) && id > 0);

                    if (!ids.length) return;

                    const parentRaw = list.dataset.parentId;
                    groups.push({
                        parent_id: parentRaw === '' ? null : Number(parentRaw),
                        item_ids: ids,
                    });
                });
                return groups;
            }

            itemNodes.forEach((node) => {
                const handle = node.querySelector('.drag-handle');
                if (!handle) return;

                handle.addEventListener('mousedown', function () {
                    node.setAttribute('draggable', 'true');
                });

                node.addEventListener('dragstart', function (e) {
                    dragNode = node;
                    node.classList.add('opacity-60');
                    e.dataTransfer.effectAllowed = 'move';
                });

                node.addEventListener('dragend', function () {
                    dragNode = null;
                    cleanDragStyles();
                });

                node.addEventListener('dragover', function (e) {
                    if (!dragNode || dragNode === node) return;

                    const sourceList = parentListOf(dragNode);
                    const targetList = parentListOf(node);
                    if (!sourceList || !targetList || sourceList !== targetList) return;

                    e.preventDefault();
                    node.classList.add('ring-1', 'ring-blue-300');
                    const rect = node.getBoundingClientRect();
                    const before = e.clientY < rect.top + rect.height / 2;
                    if (before) {
                        node.parentNode.insertBefore(dragNode, node);
                    } else {
                        node.parentNode.insertBefore(dragNode, node.nextSibling);
                    }
                    markDirty();
                });

                node.addEventListener('dragleave', function () {
                    node.classList.remove('ring-1', 'ring-blue-300');
                });
            });

            document.addEventListener('mouseup', function () {
                if (!dragNode) cleanDragStyles();
            });

            saveBtn.addEventListener('click', function () {
                if (!dirty) return;

                saveBtn.disabled = true;
                saveBtn.textContent = 'Salvando...';

                fetch(reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ groups: collectGroups() }),
                })
                    .then((r) => r.json())
                    .then((json) => {
                        if (!json || !json.success) throw new Error('Erro ao salvar ordenação.');
                        dirty = false;
                        saveBtn.classList.add('hidden');
                    })
                    .catch(() => {
                        alert('Não foi possível salvar a ordenação agora.');
                    })
                    .finally(() => {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Salvar ordem';
                    });
            });
        })();
    </script>
@else
<!-- Menu Creation Form -->
<div class="panel-card panel-card-body max-w-lg">
    <form method="POST" action="{{ route('admin.menus.store') }}">
        @csrf
        <label class="block text-sm font-semibold text-slate-800 mb-2">Nome</label>
        <input class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 mb-4" type="text" name="name" value="{{ old('name') }}" required>

        <label class="block text-sm font-semibold text-slate-800 mb-2">Slug (opcional)</label>
        <input class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 mb-4" type="text" name="slug" value="{{ old('slug') }}">

        <label class="mb-6 flex items-center gap-2 text-sm font-medium text-slate-700">
            <input class="h-4 w-4 rounded border-gray-300" type="checkbox" name="active" value="1" checked>
            Menu ativo
        </label>

        <div class="flex flex-wrap gap-2">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-semibold transition" type="submit">Salvar</button>
            <a class="bg-gray-300 hover:bg-gray-400 text-slate-800 px-6 py-2 rounded-lg text-sm font-semibold transition text-center" href="{{ route('admin.menus.index') }}">Voltar</a>
        </div>
    </form>
</div>
@endif
@endsection
