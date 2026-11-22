<x-layouts.app title="Xác nhận mua {{ $product->code }}" robots="noindex, nofollow">
    <div class="mx-auto max-w-xl px-4 py-10">
        <h1 class="font-display text-3xl font-semibold">Xác nhận mua hàng</h1>
        <div class="mt-6 rounded-3xl border border-line bg-surface p-6">
            <p class="text-xs uppercase tracking-widest text-muted">{{ $product->code }}</p>
            <h2 class="mt-2 font-display text-2xl">{{ $product->name }}</h2>
            <p class="mt-4 text-sm text-muted">Giá: <span class="text-accent">{{ $product->formattedPrice() }}</span></p>
            <p class="text-sm text-muted">Số dư: {{ $wallet->formattedBalance() }}</p>
        </div>
        <form method="post" action="{{ route('purchases.store', $product) }}" class="mt-6">
            @csrf
            <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">Xác nhận trừ tiền và nhận tài khoản</button>
        </form>
    </div>
</x-layouts.app>
