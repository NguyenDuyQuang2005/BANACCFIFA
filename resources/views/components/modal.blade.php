@props([
    'name' => 'modal',
    'title' => '',
])

<div {{ $attributes->merge(['class' => 'hidden fixed inset-0 z-50 grid place-items-center bg-canvas/80 p-4']) }} data-modal="{{ $name }}" role="dialog" aria-modal="true" aria-labelledby="modal-{{ $name }}-title">
    <div class="w-full max-w-lg rounded-3xl border border-line bg-surface p-6 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-4">
            <h2 id="modal-{{ $name }}-title" class="font-display text-xl font-semibold">{{ $title }}</h2>
            <button type="button" class="rounded-lg border border-line px-2 py-1 text-sm text-muted hover:text-ink" data-modal-close>
                Đóng
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
