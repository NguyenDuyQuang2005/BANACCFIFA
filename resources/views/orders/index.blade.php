<x-layouts.app title="Đơn hàng của tôi" robots="noindex, nofollow">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="font-display text-3xl font-semibold">Lịch sử mua hàng</h1>
        <div class="mt-6 grid gap-3">
            @forelse ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="rounded-2xl border border-line bg-surface p-4">
                    <p class="font-medium">{{ $order->code }}</p>
                    <p class="text-sm text-muted">{{ $order->formattedTotal() }} · {{ $order->status->label() }}</p>
                </a>
            @empty
                <x-empty-state title="Chưa có đơn hàng">Bạn chưa mua tài khoản nào.</x-empty-state>
            @endforelse
        </div>
        <x-pagination :paginator="$orders" />
    </div>
</x-layouts.app>
