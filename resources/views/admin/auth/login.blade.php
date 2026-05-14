<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    },
                    colors: {
                        brand: '#1463ff',
                        ink: '#182235'
                    },
                    boxShadow: {
                        float: '0 25px 60px rgba(20, 99, 255, 0.18)'
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-[#f3f7fd] font-sans text-ink">
<div class="relative min-h-screen overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(20,99,255,0.16),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(20,99,255,0.10),_transparent_26%)]"></div>
    <div class="relative min-h-screen grid place-items-center p-4 lg:p-8">
        <div class="grid w-full max-w-6xl items-center gap-8 lg:grid-cols-[1.1fr_520px]">
            <div class="hidden lg:block">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Origem Brasileira</p>
                <h1 class="mt-5 max-w-xl text-6xl font-extrabold leading-[1.02] text-ink">Um admin bonito de verdade para sua loja.</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-slate-500">Visual limpo, hierarquia forte e uma experiência parecida com plataformas modernas de e-commerce.</p>

                <div class="mt-10 grid max-w-xl gap-4 sm:grid-cols-3">
                    <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-lg shadow-blue-100/50 backdrop-blur">
                        <p class="text-3xl font-extrabold text-brand">01</p>
                        <p class="mt-2 text-sm font-semibold text-ink">Gestão rápida</p>
                    </div>
                    <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-lg shadow-blue-100/50 backdrop-blur">
                        <p class="text-3xl font-extrabold text-brand">02</p>
                        <p class="mt-2 text-sm font-semibold text-ink">Layout limpo</p>
                    </div>
                    <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-lg shadow-blue-100/50 backdrop-blur">
                        <p class="text-3xl font-extrabold text-brand">03</p>
                        <p class="mt-2 text-sm font-semibold text-ink">Experiência premium</p>
                    </div>
                </div>
            </div>

            <form class="rounded-[32px] border border-white/80 bg-white/90 p-7 shadow-float backdrop-blur lg:p-9" method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="mb-8 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand/10 text-brand">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 7h10a4 4 0 0 1 0 8H7a4 4 0 1 1 0-8Z"/>
                            <path d="M7 9.5A3.5 3.5 0 1 0 7 16.5h2.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Painel administrativo</p>
                        <h2 class="mt-1 text-2xl font-extrabold text-ink">Entrar</h2>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ $errors->first() }}</div>
                @endif

                <label class="mb-2 block text-sm font-semibold text-ink">Email</label>
                <input class="mb-4 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-ink outline-none transition focus:border-brand focus:bg-white focus:ring-4 focus:ring-brand/10" type="email" name="email" value="{{ old('email') }}" required>

                <label class="mb-2 block text-sm font-semibold text-ink">Senha</label>
                <input class="mb-4 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-ink outline-none transition focus:border-brand focus:bg-white focus:ring-4 focus:ring-brand/10" type="password" name="password" required>

                <label class="mb-6 flex items-center gap-2 text-sm font-medium text-slate-600">
                    <input class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand" type="checkbox" name="remember" value="1">
                    Lembrar acesso
                </label>

                <button class="w-full rounded-2xl bg-brand px-4 py-3 font-bold text-white shadow-float transition hover:bg-[#104ec8]" type="submit">Entrar no painel</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
