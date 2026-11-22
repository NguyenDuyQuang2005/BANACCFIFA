@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => '',
])

<label class="grid gap-2 text-sm">
    @if ($label)
        <span class="font-medium text-ink">{{ $label }}</span>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-line bg-canvas px-3 py-2.5 text-ink outline-none transition placeholder:text-muted focus:border-accent']) }}
    >
</label>
