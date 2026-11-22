<x-layouts.admin title="Đơn hàng">
    <h1 class="font-display text-3xl font-semibold">Đơn hàng</h1>
    <div class="mt-8 grid gap-3">
        @forelse ($orders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="rounded-2xl border border-line bg-surface p-4">
                <p class="font-medium">{{ $order->code }} · {{ $order->formattedTotal() }}</p>
                <p class="text-sm text-muted">{{ $order->user?->email }} · {{ $order->status->label() }}</p>
            </a>
        @empty
            <p class="text-sm text-muted">Chưa có đơn hàng.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$orders" />
</x-layouts.admin>
