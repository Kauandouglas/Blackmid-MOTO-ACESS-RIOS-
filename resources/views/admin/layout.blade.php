<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - Moto Acessórios</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="apple-touch-icon" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#1463ff',
                        brandDark: '#104ec8',
                        ink: '#182235',
                        muted: '#6f7a8f',
                        line: '#e6ebf4',
                        shell: '#f5f7fb',
                        cloud: '#f8fbff'
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 10px 30px rgba(15, 34, 68, 0.08)',
                        float: '0 18px 45px rgba(20, 99, 255, 0.12)'
                    }
                }
            }
        };
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .panel-card {
                @apply rounded-2xl sm:rounded-3xl border border-line bg-white shadow-soft;
            }

            .panel-card-body {
                @apply p-4 sm:p-5 lg:p-6;
            }

            .panel-section-title {
                @apply text-base sm:text-lg font-bold text-ink;
            }

            .panel-label {
                @apply mb-2 block text-sm font-semibold text-ink;
            }

            .panel-input {
                @apply w-full rounded-xl sm:rounded-2xl border border-line bg-white px-3 py-2.5 sm:px-4 sm:py-3 text-sm text-ink outline-none transition placeholder:text-slate-400 focus:border-brand focus:ring-4 focus:ring-brand/10;
            }

            .panel-select {
                @apply panel-input;
            }

            .panel-textarea {
                @apply panel-input min-h-[120px] resize-y;
            }

            .panel-btn-primary {
                @apply inline-flex items-center justify-center rounded-xl sm:rounded-2xl bg-brand px-3.5 py-2 sm:px-4 sm:py-2.5 text-sm font-bold text-white shadow-float transition hover:bg-brandDark;
            }

            .panel-btn-secondary {
                @apply inline-flex items-center justify-center rounded-xl sm:rounded-2xl border border-line bg-white px-3.5 py-2 sm:px-4 sm:py-2.5 text-sm font-bold text-ink transition hover:bg-slate-50;
            }

            .panel-btn-danger {
                @apply inline-flex items-center justify-center rounded-xl sm:rounded-2xl bg-rose-600 px-3.5 py-2 sm:px-4 sm:py-2.5 text-sm font-bold text-white transition hover:bg-rose-700;
            }

            .panel-badge {
                @apply inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold sm:px-2.5 sm:py-1 sm:text-xs;
            }

            .panel-badge-blue {
                @apply panel-badge bg-brand/10 text-brand;
            }

            .panel-badge-green {
                @apply panel-badge bg-emerald-100 text-emerald-700;
            }

            .panel-badge-gray {
                @apply panel-badge bg-slate-100 text-slate-600;
            }

            .panel-badge-amber {
                @apply panel-badge bg-amber-100 text-amber-700;
            }

            .panel-table-wrap {
                @apply overflow-x-auto;
            }

            .panel-table {
                @apply min-w-full text-sm;
            }

            .panel-thead {
                @apply bg-cloud text-muted;
            }

            .panel-th {
                @apply px-3 py-2.5 text-left text-[10px] sm:text-xs font-bold uppercase tracking-[0.08em] sm:px-5 sm:py-3 whitespace-nowrap;
            }

            .panel-td {
                @apply px-3 py-2.5 align-middle text-xs sm:text-sm text-slate-600 sm:px-5 sm:py-4;
            }

            .panel-td-strong {
                @apply panel-td font-semibold text-ink;
            }

            .panel-table-body {
                @apply divide-y divide-line;
            }
        }
    </style>
    <style>
        #admin-sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #sidebar-overlay {
            transition: opacity 0.3s ease;
        }
        #admin-sidebar::-webkit-scrollbar { width: 4px; }
        #admin-sidebar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }
    </style>
</head>
<body class="bg-shell font-sans text-ink antialiased">

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none lg:hidden"></div>

<div class="min-h-screen lg:grid lg:grid-cols-[272px_1fr]">

    {{-- Sidebar --}}
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-[272px] -translate-x-full flex-col overflow-y-auto border-r border-line bg-white lg:static lg:translate-x-0">

        {{-- Logo + Close --}}
        <div class="flex items-center justify-between border-b border-line px-5 py-4">
            <a href="/" target="_blank" class="block">
                <img src="/logo.png" alt="Moto Acessórios" class="h-12 w-auto object-contain">
            </a>
            <button type="button" onclick="toggleSidebar()" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition lg:hidden" aria-label="Fechar menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-3 py-4">
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Gestão</p>
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Início
                </a>
                <a href="{{ route('admin.orders.index') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.orders.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Vendas
                </a>
                <a href="{{ route('admin.abandoned-carts.index') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.abandoned-carts.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    Carrinhos
                </a>
                <a href="{{ route('admin.produtos.index') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.produtos.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.91 8.84 8.56 2.23a1.93 1.93 0 0 0-1.12 0L3.1 4.13a2 2 0 0 0-1 1.76v4.77a2 2 0 0 0 1.12 1.81l12.35 6.61a1.93 1.93 0 0 0 1.12 0l4.34-1.9a2 2 0 0 0 1-1.76V10.6a2 2 0 0 0-1.12-1.76Z"/><path d="m3.09 8.84 12.35-6.61a1.93 1.93 0 0 1 1.12 0l4.34 1.9"/><line x1="12" y1="22.08" x2="12" y2="11.5"/></svg>
                    Produtos
                </a>
                <a href="{{ route('admin.bling.products.index') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.bling.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/></svg>
                    Importar Bling
                </a>
                <a href="{{ route('admin.bling.auth') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.bling.auth') || request()->routeIs('admin.bling.connect') || request()->routeIs('admin.bling.callback') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Conectar Bling
                </a>
                <a href="{{ route('admin.categorias.index') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.categorias.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M7 12h10"/><path d="M10 18h4"/></svg>
                    Categorias
                </a>
                <a href="{{ route('admin.menus.index') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.menus.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                    Navegação
                </a>
            </nav>
        </div>

        <div class="px-3 pb-4">
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Configuração</p>
            <nav class="space-y-1">
                <a href="{{ route('admin.settings.edit') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.settings.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    Loja
                </a>
                <a href="{{ route('admin.pixel-marketing.edit') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.pixel-marketing.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"/></svg>
                    Pixels &amp; Marketing
                </a>
                <a href="{{ route('admin.payments.edit') }}" onclick="closeSidebarOnMobile()" class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.payments.*') ? 'bg-brand/10 text-brand' : 'text-ink hover:bg-slate-50' }}">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Pagamentos
                </a>
            </nav>
        </div>

        {{-- Status card --}}
        <div class="mx-3 mb-3 mt-auto hidden sm:block rounded-2xl bg-gradient-to-br from-brand to-[#4f8cff] p-4 text-white shadow-float">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/70">Status</p>
            <h3 class="mt-1.5 text-base font-extrabold leading-tight">Painel pronto</h3>
            <p class="mt-1 text-xs text-white/80">Gerencie produtos, pedidos e configurações.</p>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="min-w-0 p-3 sm:p-4 lg:p-7 xl:p-8">
        {{-- Top bar --}}
        <div class="mb-4 sm:mb-6 flex items-center gap-3 rounded-2xl sm:rounded-3xl border border-line bg-white px-3 py-3 sm:px-5 sm:py-4 shadow-soft">
            {{-- Hamburger --}}
            <button type="button" onclick="toggleSidebar()" class="shrink-0 rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-ink transition lg:hidden" aria-label="Abrir menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>

            <div class="min-w-0 flex-1">
                <p class="hidden text-xs font-bold uppercase tracking-[0.18em] text-slate-400 sm:block">Painel administrativo</p>
                <h1 class="truncate text-lg font-extrabold tracking-tight text-ink sm:mt-1 sm:text-2xl lg:text-3xl">@yield('heading', 'Admin')</h1>
                <p class="hidden text-sm text-muted sm:block sm:mt-1">Gestão elegante e centralizada da loja.</p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <div class="hidden rounded-2xl border border-line bg-cloud px-3 py-2 text-xs font-semibold text-muted xl:block xl:px-4 xl:py-2.5 xl:text-sm">
                    Ambiente administrativo
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button class="panel-btn-secondary !text-xs !px-3 !py-2 sm:!text-sm sm:!px-4 sm:!py-2.5" type="submit">Sair</button>
                </form>
            </div>
        </div>

        @include('admin.partials.flash')
        @yield('content')
    </main>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('admin-sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var isOpen = !sidebar.classList.contains('-translate-x-full');

    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        document.body.style.overflow = 'hidden';
    }
}

function closeSidebarOnMobile() {
    if (window.innerWidth < 1024) {
        var sidebar = document.getElementById('admin-sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }
}

document.getElementById('sidebar-overlay').addEventListener('click', toggleSidebar);

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && window.innerWidth < 1024) {
        var sidebar = document.getElementById('admin-sidebar');
        if (!sidebar.classList.contains('-translate-x-full')) {
            toggleSidebar();
        }
    }
});

window.addEventListener('resize', function () {
    if (window.innerWidth >= 1024) {
        var sidebar = document.getElementById('admin-sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }
});
</script>
@stack('scripts')
</body>
</html>
