<x-layouts.app title="Lịch sử giao dịch" meta-description="Giao dịch đã hoàn tất trên DINHTIENFCO. Thông tin cá nhân được che một phần.">
    <div class="mx-auto max-w-5xl px-4 py-10 lg:px-8">
        <h1 class="font-display text-3xl font-semibold">Lịch sử giao dịch</h1>
        <p class="mt-2 text-sm text-muted">Chỉ hiện đơn hoàn tất. Mã giao dịch và số điện thoại được che. Thông tin tài khoản game không công khai.</p>
        <div class="mt-8 grid gap-3">
            @forelse ($orders as $order)
                @php $item = $order->items->first(); @endphp
                <article class="grid gap-2 rounded-2xl border border-line bg-surface p-4 md:grid-cols-5 md:items-center">
                    <p class="font-medium">{{ $order->publicCode() }}</p>
                    <p class="md:col-span-2">{{ $item?->product_name ?? 'Tài khoản FC Online' }}</p>
                    <p class="text-accent">{{ $order->formattedTotal() }}</p>
                    <p class="text-sm text-muted">{{ $order->status->label() }} · {{ $order->completed_at?->format('d/m/Y H:i') }}</p>
                </article>
            @empty
                <x-empty-state title="Chưa có giao dịch hoàn tất">Khi có đơn hàng thật, chúng sẽ xuất hiện tại đây.</x-empty-state>
            @endforelse
        </div>
        <x-pagination :paginator="$orders" />
    </div>
</x-layouts.app>
