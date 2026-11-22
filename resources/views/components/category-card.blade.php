@props([
    'category',
])

<article {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-3xl border-2 border-primary/60 bg-surface p-2 shadow-lg shadow-primary/10 transition hover:-translate-y-1 hover:border-accent hover:shadow-accent/20']) }}>
    <a href="{{ route('catalog.category', $category) }}" class="relative block overflow-hidden rounded-2xl" tabindex="-1" aria-hidden="true">
        @if ($category->imageUrl())
            <img
                src="{{ $category->imageUrl() }}"
                alt="{{ $category->name }}"
                loading="lazy"
                class="aspect-video w-full object-cover transition duration-500 group-hover:scale-105"
            >
        @else
            <div class="pitch-grid grid aspect-video w-full place-items-center bg-gradient-to-br from-primary/40 via-canvas to-accent/20 p-6 text-center">
                <span class="font-display text-2xl font-bold uppercase leading-tight text-ink drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)]">{{ $category->name }}</span>
            </div>
        @endif
    </a>

    <h3 class="mt-2 rounded-xl bg-ink px-3 py-3 text-center font-display text-base font-bold uppercase tracking-wide text-canvas">
        <a href="{{ route('catalog.category', $category) }}" class="hover:text-primary">{{ $category->name }}</a>
    </h3>

    <div class="mt-auto px-2 pb-2 pt-3">
        @isset($category->available_products_count)
            <p class="text-sm text-muted">Còn lại: <span class="font-semibold text-accent">{{ number_format($category->available_products_count) }}</span></p>
        @endisset
        <a
            href="{{ route('catalog.category', $category) }}"
            class="mt-3 block rounded-xl bg-accent px-4 py-3 text-center text-sm font-bold uppercase tracking-wider text-canvas transition hover:bg-primary hover:text-white"
        >Xem tất cả</a>
    </div>
</article>
