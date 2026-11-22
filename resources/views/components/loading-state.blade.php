<div {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-2xl border border-line bg-surface px-4 py-3 text-sm text-muted']) }} role="status">
    <span class="h-4 w-4 animate-spin rounded-full border-2 border-line border-t-accent"></span>
    <span>{{ $slot->isEmpty() ? 'Đang tải...' : $slot }}</span>
</div>
