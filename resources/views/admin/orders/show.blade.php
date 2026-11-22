<x-layouts.admin title="Đơn {{ $order->code }}">
    <h1 class="font-display text-3xl font-semibold">Đơn {{ $order->code }}</h1>
    <p class="mt-2 text-sm text-muted">{{ $order->user?->email }} · {{ $order->formattedTotal() }} · {{ $order->status->label() }}</p>
    @foreach ($order->items as $item)
        <section class="mt-6 rounded-3xl border border-line bg-surface p-6">
            <p class="text-xs uppercase tracking-widest text-muted">{{ $item->product_code }}</p>
            <h2 class="mt-2 font-display text-xl">{{ $item->product_name }}</h2>
            <p class="mt-4 whitespace-pre-wrap break-all text-sm">{{ $item->credentials ?: 'Không có thông tin đăng nhập.' }}</p>
        </section>
    @endforeach
</x-layouts.admin>
