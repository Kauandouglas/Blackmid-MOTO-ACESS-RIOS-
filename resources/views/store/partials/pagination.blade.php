@if ($paginator->hasPages())
    <nav class="store-pagination" aria-label="Paginacao de produtos">
        <div class="store-pagination__summary">
            Pagina {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
        </div>

        <div class="store-pagination__links">
            @if ($paginator->onFirstPage())
                <span class="store-pagination__control is-disabled" aria-disabled="true">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @else
                <a class="store-pagination__control" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Pagina anterior">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="store-pagination__dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="store-pagination__page is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="store-pagination__page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="store-pagination__control" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Proxima pagina">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="store-pagination__control is-disabled" aria-disabled="true">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
