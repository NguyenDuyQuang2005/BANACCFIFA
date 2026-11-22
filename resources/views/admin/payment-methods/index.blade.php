<x-layouts.admin title="Ngân hàng nạp tiền">
    <div class="flex items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-semibold">Ngân hàng nạp tiền</h1>
        <a href="{{ route('admin.payment-methods.create') }}" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Thêm STK</a>
    </div>
    <p class="mt-2 text-sm text-muted">Chỉ nhập số tài khoản thật. Shop không tự sinh dữ liệu ngân hàng.</p>
    <div class="mt-8 grid gap-3">
        @forelse ($methods as $method)
            <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">
                <div>
                    <p class="font-medium">{{ $method->name }} · {{ $method->account_number }}</p>
                    <p class="text-sm text-muted">{{ $method->account_name }} · {{ $method->is_active ? 'Đang dùng' : 'Tắt' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.payment-methods.edit', $method) }}" class="rounded-full border border-line px-4 py-2 text-xs">Sửa</a>
                    <form method="post" action="{{ route('admin.payment-methods.destroy', $method) }}">
                        @csrf
                        @method('delete')
                        <button class="rounded-full border border-line px-4 py-2 text-xs text-primary">Xóa / tắt</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có phương thức nạp. Thành viên sẽ thấy thông báo trống trên trang nạp tiền.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$methods" />
</x-layouts.admin>
