<nav role="navigation" class="pagination-nav" aria-label="Pagination">
    @if ($paginator->onFirstPage())
        <span class="page-btn disabled">← Anterior</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn">← Anterior</a>
    @endif

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn">Siguiente →</a>
    @else
        <span class="page-btn disabled">Siguiente →</span>
    @endif
</nav>
