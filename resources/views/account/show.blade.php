<x-layouts.app title="Tài khoản cá nhân" robots="noindex, nofollow">
    <div class="mx-auto max-w-5xl px-4 py-10 lg:px-8">
        <h1 class="font-display text-3xl font-semibold">Tài khoản cá nhân</h1>
        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="rounded-3xl border border-line bg-surface p-6">
                <p class="text-xs uppercase tracking-widest text-muted">Số dư</p>
                <p class="mt-2 font-display text-4xl font-semibold text-accent">{{ $wallet->formattedBalance() }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('deposit') }}" class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Nạp tiền</a>
                    <a href="{{ route('affiliate.show') }}" class="inline-flex rounded-full border border-accent px-4 py-2 text-sm font-semibold text-accent">Giới thiệu bạn bè · hoa hồng {{ number_format((float) $user->commission_balance, 0, ',', '.') }}₫</a>
                </div>
            </section>
            <section class="rounded-3xl border border-line bg-surface p-6">
                <h2 class="font-display text-lg font-semibold">Thông tin</h2>
                <form method="post" action="{{ route('account.update') }}" class="mt-4 grid gap-4">
                    @csrf
                    @method('put')
                    <x-form-input name="name" label="Họ tên" :value="old('name', $user->name)" required />
                    <x-form-input name="phone" label="Số điện thoại" :value="old('phone', $user->phone)" />
                    <p class="text-sm text-muted">Email: {{ $user->email }}</p>
                    <button class="rounded-full border border-line px-4 py-2 text-sm">Lưu thông tin</button>
                </form>
            </section>
        </div>

        <section class="mt-8 rounded-3xl border border-line bg-surface p-6">
            <h2 class="font-display text-lg font-semibold">Đổi mật khẩu</h2>
            <form method="post" action="{{ route('account.password') }}" class="mt-4 grid gap-4 md:grid-cols-3">
                @csrf
                @method('put')
                <x-form-input name="current_password" type="password" label="Mật khẩu hiện tại" required />
                <x-form-input name="password" type="password" label="Mật khẩu mới" required />
                <x-form-input name="password_confirmation" type="password" label="Nhập lại" required />
                @error('current_password') <p class="text-sm text-primary md:col-span-3">{{ $message }}</p> @enderror
                @error('password') <p class="text-sm text-primary md:col-span-3">{{ $message }}</p> @enderror
                <button class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white md:col-span-3">Đổi mật khẩu</button>
            </form>
        </section>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section>
                <div class="mb-3 flex justify-between">
                    <h2 class="font-display text-lg font-semibold">Nạp tiền gần đây</h2>
                    <a href="{{ route('deposit') }}" class="text-sm text-accent">Xem tất cả</a>
                </div>
                @forelse ($deposits as $deposit)
                    <a href="{{ route('deposits.show', $deposit) }}" class="mb-2 block rounded-2xl border border-line bg-surface p-4 text-sm">
                        {{ $deposit->code }} · {{ $deposit->formattedAmount() }} · {{ $deposit->status->label() }}
                    </a>
                @empty
                    <p class="text-sm text-muted">Chưa có lệnh nạp.</p>
                @endforelse
            </section>
            <section>
                <div class="mb-3 flex justify-between">
                    <h2 class="font-display text-lg font-semibold">Đơn hàng</h2>
                    <a href="{{ route('orders.index') }}" class="text-sm text-accent">Xem tất cả</a>
                </div>
                @forelse ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="mb-2 block rounded-2xl border border-line bg-surface p-4 text-sm">
                        {{ $order->code }} · {{ $order->formattedTotal() }}
                    </a>
                @empty
                    <p class="text-sm text-muted">Chưa mua tài khoản.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-layouts.app>
