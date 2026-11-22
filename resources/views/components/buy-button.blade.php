@props([
    'product',
    'label' => 'Mua ngay',
    'size' => 'sm',
])

@php
    $sizeClass = $size === 'lg' ? 'px-7 py-3 text-sm' : 'px-3 py-2 text-xs';
@endphp

@if ($product->isAvailable())
    <button
        type="button"
        {{ $attributes->merge(['class' => 'rounded-full bg-primary font-semibold text-white hover:bg-primary-dark '.$sizeClass]) }}
        data-buy
        data-buy-code="{{ $product->code }}"
        data-buy-name="{{ $product->name }}"
        data-buy-image="{{ $product->coverUrl() }}"
        data-buy-quote-url="{{ route('purchases.quote', $product) }}"
        data-buy-url="{{ route('purchases.store', $product) }}"
    >{{ $label }}</button>
@else
    <span {{ $attributes->merge(['class' => 'cursor-not-allowed rounded-full border border-line font-semibold text-muted '.$sizeClass]) }}>Đã bán</span>
@endif
