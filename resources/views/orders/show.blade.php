<x-layouts.app title="Đơn {{ $order->code }}" robots="noindex, nofollow">
    <div class="mx-auto max-w-2xl px-4 py-10">
        <h1 class="font-display text-3xl font-semibold">Đơn hàng {{ $order->code }}</h1>
        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif
        <p class="mt-2 text-sm text-muted">{{ $order->formattedTotal() }} · {{ $order->status->label() }}</p>
        @foreach ($order->items as $item)
            <section class="mt-6 rounded-3xl border border-line bg-surface p-6">
                <p class="text-xs uppercase tracking-widest text-muted">{{ $item->product_code }}</p>
                <h2 class="mt-2 font-display text-xl">{{ $item->product_name }}</h2>
                @if ($item->credentials)
                    <div class="mt-4 rounded-2xl bg-canvas p-4 text-sm">
                        <p class="text-muted">Thông tin tài khoản game — chỉ dành cho bạn</p>
                        <p class="mt-2 whitespace-pre-wrap break-all">{{ $item->credentials }}</p>
                    </div>
                @else
                    <p class="mt-4 text-sm text-muted">Chưa có thông tin đăng nhập được lưu cho đơn này.</p>
                @endif
            </section>
        @endforeach
    </div>
</x-layouts.app>
