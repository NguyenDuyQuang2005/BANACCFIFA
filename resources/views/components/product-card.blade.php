@props([
    'product',
])

<article {{ $attributes->merge(['class' => 'flex h-full flex-col overflow-hidden rounded-3xl border border-line bg-surface']) }}>
    <div class="relative aspect-[16/10] overflow-hidden bg-surface-2" data-product-cover>
        @if ($product->coverUrl())
            <img src="{{ $product->coverUrl() }}" alt="{{ $product->name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
        @else
            <div class="grid h-full place-items-center text-muted">Chưa có hình</div>
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-canvas/80 px-3 py-1 text-xs font-medium text-accent">{{ $product->status->label() }}</span>
        @if ($product->discountPercent())
            <span class="absolute right-3 top-3 rounded-full bg-primary px-3 py-1 text-xs font-bold text-white" data-discount-badge>-{{ $product->discountPercent() }}%</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-3 p-5">
        <p class="text-xs uppercase tracking-widest text-muted">{{ $product->code }}</p>
        <h3 class="font-display text-lg font-semibold leading-snug">{{ $product->name }}</h3>
        @if ($product->description)
            <p class="line-clamp-2 text-sm text-muted">{{ $product->description }}</p>
        @endif
        <dl class="grid grid-cols-3 gap-2 text-xs text-muted">
            <div>
                <dt>BP</dt>
                <dd class="text-ink">{{ $product->formattedBp() }}</dd>
            </div>
            <div>
                <dt>Đội hình</dt>
                <dd class="text-ink">{{ $product->squad ?? '—' }}</dd>
            </div>
            <div>
                <dt>OVR</dt>
                <dd class="text-ink">{{ $product->ovr ?? '—' }}</dd>
            </div>
        </dl>
        <div class="mt-auto flex items-end justify-between gap-3 pt-2">
            <div>
                @if ($product->formattedOriginalPrice())
                    <p class="text-xs text-muted line-through">{{ $product->formattedOriginalPrice() }}</p>
                @endif
                <p class="font-display text-xl font-semibold text-accent">{{ $product->formattedPrice() }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('catalog.show', $product) }}" class="rounded-full border border-line px-3 py-2 text-xs font-semibold text-ink hover:border-accent">
                    Chi tiết
                </a>
                <x-buy-button :product="$product" />
            </div>
        </div>
    </div>
</article>
