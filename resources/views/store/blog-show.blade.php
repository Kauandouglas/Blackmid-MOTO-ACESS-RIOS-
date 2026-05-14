@extends('layouts.app')

@section('title', $post['title'] . ' - Blog Origem Brasileira')

@section('content')
<style>
    .blog-content {
        color: #4b5563;
        line-height: 1.8;
        font-size: 1.06rem;
    }

    .blog-content h2 {
        color: #111827;
        font-size: 1.55rem;
        margin-top: 1.65rem;
        margin-bottom: 0.8rem;
        font-weight: 700;
    }

    .blog-content h3 {
        color: #111827;
        font-size: 1.25rem;
        margin-top: 1.3rem;
        margin-bottom: 0.65rem;
        font-weight: 700;
    }

    .blog-content p { margin-bottom: 1rem; }
    .blog-content ul, .blog-content ol { margin: 0.8rem 0 1rem 1.2rem; }
    .blog-content li { margin-bottom: 0.35rem; }
    .blog-content a { color: #182235; text-decoration: underline; }
    .blog-content img {
        border-radius: 0.7rem;
        margin: 1.1rem 0;
        width: 100%;
        height: auto;
        display: block;
    }
    .blog-content blockquote {
        border-left: 3px solid #d1d5db;
        padding-left: 1rem;
        color: #6b7280;
        font-style: italic;
        margin: 1rem 0;
    }
</style>
<section class="max-w-[1100px] mx-auto px-4 lg:px-8 py-10 lg:py-14">
    <nav class="flex items-center gap-2 text-xs text-muted mb-8 tracking-wide">
        <a href="{{ route('store.index') }}" class="hover:text-ink transition">Loja</a>
        <span>›</span>
        <a href="{{ route('store.blog') }}" class="hover:text-ink transition">Blog</a>
        <span>›</span>
        <span class="text-ink">{{ $post['title'] }}</span>
    </nav>

    <div class="max-w-3xl mx-auto">
        <p class="text-xs uppercase tracking-[.18em] text-muted mb-3">{{ $post['category'] }} · {{ $post['published_at'] }}</p>
        <h1 class="font-display text-4xl lg:text-6xl text-ink leading-[0.95] mb-5">{{ $post['title'] }}</h1>
        <p class="text-lg text-muted leading-relaxed mb-8">{{ $post['subtitle'] ?? $post['excerpt'] }}</p>
    </div>

    <div class="aspect-[16/8] overflow-hidden bg-gray-100 mb-10 lg:mb-14">
        <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="w-full h-full object-cover">
    </div>

    <article class="max-w-3xl mx-auto blog-content">
        {!! $post['content_html'] ?? '' !!}
    </article>

    <div class="max-w-3xl mx-auto mt-14 pt-8 border-t border-gray-200">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl text-ink font-semibold">Continue lendo</h2>
            <a href="{{ route('store.blog') }}" class="text-xs uppercase tracking-[.18em] text-muted hover:text-ink transition">Ver blog</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($relatedPosts as $related)
                <a href="{{ route('store.blog.show', $related['slug']) }}" class="group block border border-gray-200 overflow-hidden bg-white">
                    <div class="aspect-[4/3] overflow-hidden">
                        <img src="{{ $related['image'] }}" alt="{{ $related['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-4">
                        <p class="text-[11px] uppercase tracking-[.12em] text-muted mb-2">{{ $related['category'] }}</p>
                        <h3 class="text-sm font-medium text-ink leading-snug">{{ $related['title'] }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
