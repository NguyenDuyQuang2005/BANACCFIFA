@props([
    'type' => 'info',
])

@php
    $styles = [
        'info' => 'border-line bg-surface-2 text-ink',
        'success' => 'border-accent/40 bg-accent/10 text-ink',
        'warning' => 'border-accent/50 bg-accent/15 text-accent',
        'danger' => 'border-primary/50 bg-primary/10 text-ink',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border px-4 py-3 text-sm '.$styles[$type]]) }} role="status">
    {{ $slot }}
</div>
