@props([
    'size' => 'md',
])

@php
    $mark = $size === 'lg' ? 'h-12 w-12' : 'h-10 w-10';
    $word = $size === 'lg' ? 'text-xl' : 'text-base';
@endphp

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="{{ $mark }} relative grid place-items-center overflow-hidden rounded-xl border border-accent/40 bg-surface-2 shadow-[0_0_24px_rgba(214,31,38,0.25)]">
        <span class="absolute inset-x-0 top-0 h-0.5 bg-accent"></span>
        <span class="font-display text-sm font-bold tracking-tight text-accent" aria-hidden="true">DT</span>
    </span>
    <span class="leading-tight">
        <span class="font-display {{ $word }} font-semibold tracking-[0.18em] text-ink">DINHTIENFCO</span>
        <span class="block text-[11px] uppercase tracking-[0.22em] text-muted">FC Online</span>
    </span>
    <span class="sr-only">{{ config('site.name') }} — Trang chủ</span>
</a>
