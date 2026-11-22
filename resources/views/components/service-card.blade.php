@props([
    'service',
])

@php
    $contactUrl = $service->contactUrl();
    $isExternal = $contactUrl && str_starts_with($contactUrl, 'http');
    $linkAttributes = $contactUrl
        ? 'href="'.e($contactUrl).'"'.($isExternal ? ' target="_blank" rel="noopener nofollow"' : '')
        : '';
@endphp

<article {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-3xl border border-line bg-surface transition hover:-translate-y-1 hover:border-accent/60 hover:shadow-lg hover:shadow-accent/10']) }} data-service-card>
    <{{ $contactUrl ? 'a' : 'div' }} {!! $linkAttributes !!} class="relative block overflow-hidden" tabindex="-1" aria-hidden="true">
        @if ($service->imageUrl())
            <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" loading="lazy" class="aspect-[2/1] w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="pitch-grid grid aspect-[2/1] w-full place-items-center bg-gradient-to-br from-primary/40 via-canvas to-accent/20 p-6">
                <span class="font-display text-2xl font-bold uppercase text-ink">Dịch vụ FCO</span>
            </div>
        @endif
        @if ($service->code)
            <span class="absolute right-3 top-3 rounded-lg bg-canvas/85 px-2.5 py-1 font-display text-sm font-bold text-ink backdrop-blur">{{ $service->code }}</span>
        @endif
    </{{ $contactUrl ? 'a' : 'div' }}>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-display text-base font-semibold leading-snug text-ink">{{ $service->name }}</h3>
        @if ($service->description)
            <p class="mt-2 line-clamp-3 whitespace-pre-line text-sm italic leading-6 text-muted">{{ $service->description }}</p>
        @endif
        @if ($contactUrl)
            <a {!! $linkAttributes !!} class="mt-auto pt-4 font-display text-base font-bold text-primary hover:text-accent">
                {{ $service->contact_label ?: 'Liên hệ admin' }}
            </a>
        @else
            <p class="mt-auto pt-4 text-sm text-muted">Chưa cấu hình link liên hệ.</p>
        @endif
    </div>
</article>
