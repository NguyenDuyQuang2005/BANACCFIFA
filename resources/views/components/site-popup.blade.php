@props([
    'popup',
])

@php
    $isExternal = $popup->button_url && str_starts_with($popup->button_url, 'http');
@endphp

<div
    class="fixed inset-0 z-[60] hidden place-items-center bg-black/70 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="site-popup-title"
    data-site-popup
    data-popup-key="{{ $popup->dismissKey() }}"
    data-popup-minutes="{{ $popup->dismiss_minutes }}"
>
    <div class="relative w-full max-w-md overflow-hidden rounded-3xl border border-primary/40 bg-surface shadow-2xl shadow-primary/20" data-site-popup-panel>
        <button
            type="button"
            class="absolute right-3 top-3 z-10 grid h-9 w-9 place-items-center rounded-full bg-canvas/80 text-lg leading-none text-ink hover:bg-primary hover:text-white"
            aria-label="Đóng"
            data-site-popup-close
        >×</button>

        @if ($popup->mediaUrl() && $popup->media_type === 'image')
            <img src="{{ $popup->mediaUrl() }}" alt="{{ $popup->title }}" class="max-h-[50vh] w-full object-cover">
        @elseif ($popup->mediaUrl() && $popup->media_type === 'video')
            <video src="{{ $popup->mediaUrl() }}" class="max-h-[50vh] w-full bg-black" controls muted playsinline preload="metadata" data-site-popup-video></video>
        @endif

        <div class="p-6 text-center">
            <h2 id="site-popup-title" class="font-display text-2xl font-semibold text-accent">{{ $popup->title }}</h2>
            @if ($popup->content)
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-ink">{{ $popup->content }}</p>
            @endif
            @if ($popup->button_url)
                <a
                    href="{{ $popup->button_url }}"
                    @if ($isExternal) target="_blank" rel="noopener nofollow" @endif
                    class="mt-5 inline-flex rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark"
                >{{ $popup->button_label ?: 'Xem ngay' }}</a>
            @endif
            <button type="button" class="mt-4 block w-full text-xs text-muted hover:text-ink" data-site-popup-close>
                Đóng, ẩn trong {{ $popup->dismiss_minutes >= 60 && $popup->dismiss_minutes % 60 === 0 ? ($popup->dismiss_minutes / 60).' giờ' : $popup->dismiss_minutes.' phút' }}
            </button>
        </div>
    </div>
</div>
