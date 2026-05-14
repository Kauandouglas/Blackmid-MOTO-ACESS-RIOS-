@extends('admin.layout')

@section('title', $post->exists ? 'Editar post' : 'Novo post')
@section('heading', $post->exists ? 'Editar post' : 'Novo post')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">
<style>
    #blogContentEditor .ql-editor {
        min-height: 300px;
        font-size: 0.95rem;
        line-height: 1.75;
    }

    #blogContentEditor .ql-editor h2 {
        font-size: 1.35rem;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    #blogContentEditor .ql-editor h3 {
        font-size: 1.12rem;
        margin-top: 0.8rem;
        margin-bottom: 0.45rem;
        font-weight: 700;
    }
</style>
@php
    $subtitleValue = old('excerpt', $post->excerpt);
    $contentValue = old('content', $post->content);
@endphp
<div class="panel-card panel-card-body">
    <form id="blogPostForm" method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.blogs.update', $post) : route('admin.blogs.store') }}" class="space-y-5">
        @csrf
        @if($post->exists)
            @method('PUT')
        @endif

        <div>
            <label class="panel-label">Título</label>
            <input class="panel-input" type="text" name="title" value="{{ old('title', $post->title) }}" maxlength="220" required>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="panel-label">Categoria</label>
                <input class="panel-input" type="text" name="category" value="{{ old('category', $post->category) }}" maxlength="120" required>
            </div>
            <div class="md:col-span-2 rounded-2xl border border-line bg-cloud px-4 py-3">
                <p class="text-sm font-semibold text-ink">Publicação automática</p>
                <p class="mt-1 text-xs text-slate-500">
                    Tempo de leitura e data de publicação são calculados automaticamente ao salvar.
                    @if($post->exists && $post->published_at)
                        Publicado em: {{ optional($post->published_at)->format('d/m/Y H:i') }}.
                    @endif
                </p>
            </div>
        </div>

        <div>
            <label class="panel-label">Imagem de capa</label>
            <input class="panel-input" type="file" name="image_file" id="coverImageFile" accept="image/*" {{ $post->exists ? '' : 'required' }}>
            <p class="mt-2 text-xs text-slate-500">Upload obrigatório para novos posts. A imagem é otimizada automaticamente no envio.</p>
            @if($post->image)
                <div class="mt-3 overflow-hidden rounded-2xl border border-line bg-white">
                    <img src="{{ $post->image }}" alt="Capa atual" class="h-52 w-full object-cover">
                </div>
            @endif
        </div>

        <div>
            <label class="panel-label">Subtítulo</label>
            <textarea class="panel-textarea" name="excerpt" rows="3" required>{{ $subtitleValue }}</textarea>
        </div>

        <div>
            <label class="panel-label">Conteúdo</label>
            <p class="mb-2 text-xs text-slate-500">Editor moderno com negrito, listas, links e upload de imagens direto no texto.</p>
            <div id="blogContentEditor" class="overflow-hidden rounded-2xl border border-line bg-white"></div>
            <textarea class="hidden" name="content" id="blogContentField">{{ $contentValue }}</textarea>
            <p id="contentError" class="hidden mt-2 text-xs text-red-500">O conteúdo não pode ficar vazio.</p>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
            <input class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand" type="checkbox" name="active" value="1" {{ old('active', $post->active ?? true) ? 'checked' : '' }}>
            Post ativo (visível na loja)
        </label>

        <div class="flex flex-wrap gap-2">
            <button class="panel-btn-primary" type="submit">Salvar</button>
            <a class="panel-btn-secondary" href="{{ route('admin.blogs.index') }}">Voltar</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
    (function () {
        const form = document.getElementById('blogPostForm');
        const contentField = document.getElementById('blogContentField');
        const csrfToken = form ? (form.querySelector('input[name="_token"]')?.value || '') : '';

        const quill = new Quill('#blogContentEditor', {
            theme: 'snow',
            placeholder: 'Escreva seu artigo aqui...',
            modules: {
                toolbar: {
                    container: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'link', 'image'],
                        ['clean']
                    ],
                    handlers: {
                        image: imageHandler,
                    }
                }
            }
        });

        const initialContent = (contentField?.value || '').trim();
        if (initialContent) {
            if (initialContent.startsWith('<')) {
                quill.clipboard.dangerouslyPasteHTML(initialContent);
            } else {
                quill.setText(initialContent);
            }
        }

        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async () => {
                const file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) {
                    return;
                }

                const body = new FormData();
                body.append('image', file);

                try {
                    const response = await fetch('{{ route('admin.blogs.upload-image') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body,
                    });

                    if (!response.ok) {
                        throw new Error('Falha no upload da imagem');
                    }

                    const result = await response.json();
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range ? range.index : 0, 'image', result.url, 'user');
                    quill.setSelection((range ? range.index : 0) + 1, 0);
                } catch (error) {
                    alert('Não foi possível enviar a imagem. Tente novamente.');
                }
            };
        }

        if (form && contentField) {
            form.addEventListener('submit', function (e) {
                const html = quill.getSemanticHTML();
                const plain = quill.getText().trim();
                const errorEl = document.getElementById('contentError');

                if (!plain) {
                    e.preventDefault();
                    if (errorEl) errorEl.classList.remove('hidden');
                    quill.focus();
                    return;
                }

                if (errorEl) errorEl.classList.add('hidden');
                contentField.value = html;
            });

            quill.on('text-change', function () {
                const errorEl = document.getElementById('contentError');
                if (errorEl && quill.getText().trim()) {
                    errorEl.classList.add('hidden');
                }
            });
        }
    })();
</script>
@endsection
