@props([
    'text',
    'url' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-primary/30 bg-primary/10 px-4 py-3 text-sm']) }} data-zalo-notice>
    @if ($url)
        <a href="{{ $url }}" target="_blank" rel="noopener nofollow" class="flex flex-wrap items-center gap-3 hover:opacity-90">
            <span aria-hidden="true">📢</span>
            <span class="font-medium">{{ $text }}</span>
            <span class="ml-auto rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white">Nhắn Zalo →</span>
        </a>
    @else
        <p class="flex items-center gap-3">
            <span aria-hidden="true">📢</span>
            <span class="font-medium">{{ $text }}</span>
        </p>
    @endif
</div>
