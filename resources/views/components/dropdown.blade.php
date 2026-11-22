@props([
    'label',
    'align' => 'left',
])

<div {{ $attributes->merge(['class' => 'relative']) }} data-dropdown>
    <button
        type="button"
        class="inline-flex items-center gap-1 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium text-muted transition hover:bg-surface-2 hover:text-ink"
        data-dropdown-trigger
        aria-expanded="false"
        aria-haspopup="true"
    >
        {{ $label }}
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
        </svg>
    </button>
    <div
        class="{{ $align === 'right' ? 'right-0' : 'left-0' }} absolute z-40 mt-2 hidden min-w-56 rounded-2xl border border-line bg-surface p-2 shadow-2xl"
        data-dropdown-panel
        role="menu"
    >
        {{ $slot }}
    </div>
</div>
