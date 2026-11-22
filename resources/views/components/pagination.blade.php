@props([
    'paginator' => null,
])

@if ($paginator && $paginator->hasPages())
    <nav {{ $attributes->merge(['class' => 'mt-8 flex flex-wrap items-center justify-center gap-2']) }} aria-label="Phân trang">
        @if ($paginator->onFirstPage())
            <span class="rounded-full border border-line px-4 py-2 text-sm text-muted">Trước</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="rounded-full border border-line px-4 py-2 text-sm text-ink hover:border-accent">Trước</a>
        @endif

        <span class="px-3 text-sm text-muted">Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="rounded-full border border-line px-4 py-2 text-sm text-ink hover:border-accent">Sau</a>
        @else
            <span class="rounded-full border border-line px-4 py-2 text-sm text-muted">Sau</span>
        @endif
    </nav>
@endif
