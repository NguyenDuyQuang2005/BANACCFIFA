@props([
    'title',
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-line bg-surface/60 px-6 py-12 text-center']) }}>
    <p class="font-display text-lg font-semibold text-ink">{{ $title }}</p>
    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted">{{ $slot }}</p>
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="mt-6 inline-flex rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
            {{ $actionLabel }}
        </a>
    @endif
</div>
