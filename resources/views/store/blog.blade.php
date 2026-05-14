@extends('layouts.app')

@section('title', 'Blog - ' . config('app.name'))

@section('content')
<section class="max-w-[1400px] mx-auto px-4 lg:px-8 py-10 lg:py-14">
    <div class="max-w-3xl mb-10">
        <p class="text-xs uppercase tracking-[.18em] text-muted mb-3">Blog</p>
        <h1 class="font-display text-4xl lg:text-6xl text-ink leading-none mb-4">Conteúdos para motociclistas</h1>
        <p class="text-muted text-base leading-relaxed">Dicas de manutenção, escolha de acessórios, segurança, capacetes, vestuário e cuidados para rodar melhor todos os dias.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
        @foreach($posts as $post)
            <article class="group border border-gray-200 bg-white overflow-hidden">
                <a href="{{ route('store.blog.show', $post['slug']) }}" class="block aspect-[4/3] overflow-hidden bg-gray-100">
                    <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </a>
                <div class="p-5 lg:p-6">
                    <div class="flex items-center gap-3 text-[11px] uppercase tracking-[.12em] text-muted mb-3">
                        <span>{{ $post['category'] }}</span>
                        <span>{{ $post['published_at'] }}</span>
                    </div>
                    <h2 class="text-xl text-ink font-semibold leading-snug mb-3">{{ $post['title'] }}</h2>
                    <p class="text-sm text-muted leading-relaxed mb-5">{{ $post['subtitle'] ?? $post['excerpt'] }}</p>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs text-muted">{{ $post['read_time'] }}</span>
                        <a href="{{ route('store.blog.show', $post['slug']) }}" class="text-xs tracking-[.18em] uppercase text-ink hover:text-muted transition">Ler artigo</a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
