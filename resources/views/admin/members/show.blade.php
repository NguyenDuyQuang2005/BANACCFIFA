<x-layouts.admin title="{{ $member->name }}">
    <a href="{{ route('admin.members.index') }}" class="text-sm text-muted hover:text-accent">← Thành viên</a>
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <h1 class="font-display text-3xl font-semibold">{{ $member->name }}</h1>
        @if ($member->isBanned())
            <span class="rounded-full bg-primary/15 px-3 py-1 text-xs text-primary">Đã khóa từ {{ $member->banned_at->format('d/m/Y H:i') }}</span>
        @endif
    </div>
    <p class="mt-2 text-sm text-muted">{{ $member->email }} · {{ $member->phone ?? 'Chưa có SĐT' }} · Tham gia {{ $member->created_at?->format('d/m/Y') }}</p>
    <p class="mt-1 text-sm text-muted">
        Mã giới thiệu: <strong class="text-ink">{{ $member->affiliate_code }}</strong>
        · Hoa hồng: <strong class="text-ink">{{ number_format((float) $member->commission_balance, 0, ',', '.') }}₫</strong>
        · Được giới thiệu bởi: {{ $member->referrer?->email ?? 'không có' }}
    </p>
    <p class="mt-4 font-display text-2xl text-accent">Số dư: {{ $member->wallet?->formattedBalance() ?? '0₫' }}</p>

    @if ($errors->any())
        <x-alert type="danger" class="mt-6">{{ $errors->first() }}</x-alert>
    @endif

    @cannot('manage-system')
        <p class="mt-6 rounded-2xl border border-line bg-surface px-4 py-3 text-sm text-muted">Điều chỉnh số dư, phân quyền và khóa tài khoản chỉ dành cho Admin dev.</p>
    @endcannot

    @can('manage-system')
    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="rounded-3xl border border-line bg-surface p-6">
            <h2 class="font-display text-xl">Điều chỉnh số dư</h2>
            <p class="mt-1 text-xs text-muted">Chỉ dùng để sửa sai sót. Nạp tiền thường phải duyệt ở trang Duyệt nạp tiền. Mọi điều chỉnh đều được ghi vào lịch sử ví và nhật ký hoạt động.</p>
            <form method="post" action="{{ route('admin.members.balance', $member) }}" class="mt-4 grid gap-3" onsubmit="return confirm('Xác nhận điều chỉnh số dư?')">
                @csrf
                <label class="grid gap-2 text-sm">
                    <span class="font-medium">Loại</span>
                    <select name="type" class="rounded-xl border border-line bg-canvas px-3 py-2.5">
                        <option value="credit" @selected(old('type') === 'credit')>Cộng tiền</option>
                        <option value="debit" @selected(old('type') === 'debit')>Trừ tiền</option>
                    </select>
                </label>
                <x-form-input name="amount" type="number" label="Số tiền (₫)" :value="old('amount')" min="1000" step="1000" required />
                <x-form-input name="reason" label="Lý do (bắt buộc)" :value="old('reason')" minlength="5" maxlength="200" required />
                <button class="justify-self-start rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white">Lưu điều chỉnh</button>
            </form>
        </section>

        <section class="grid content-start gap-6 rounded-3xl border border-line bg-surface p-6">
            <div>
                <h2 class="font-display text-xl">Phân quyền</h2>
                <form method="post" action="{{ route('admin.members.role', $member) }}" class="mt-4 flex gap-2">
                    @csrf
                    @method('put')
                    <select name="role" class="rounded-xl border border-line bg-canvas px-3 py-2.5 text-sm">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected($member->role === $role)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-full border border-line px-4 py-2 text-sm">Cập nhật</button>
                </form>
            </div>
            <div>
                <h2 class="font-display text-xl">Bảo mật</h2>
                <form method="post" action="{{ route('admin.members.ban', $member) }}" class="mt-4" onsubmit="return confirm('Xác nhận thay đổi trạng thái khóa?')">
                    @csrf
                    <button class="rounded-full border border-primary px-4 py-2 text-sm text-primary">
                        {{ $member->isBanned() ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
                    </button>
                </form>
                <p class="mt-2 text-xs text-muted">Tài khoản bị khóa sẽ bị đăng xuất ngay và không đăng nhập lại được.</p>
            </div>
        </section>
    </div>
    @endcan

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section>
            <h2 class="font-display text-xl">Lịch sử ví</h2>
            @forelse ($walletTransactions as $transaction)
                <p class="mt-2 text-sm">
                    <span class="{{ $transaction->type === \App\Enums\WalletTransactionType::Credit ? 'text-emerald-400' : 'text-primary' }}">{{ $transaction->type === \App\Enums\WalletTransactionType::Credit ? '+' : '−' }}{{ $transaction->formattedAmount() }}</span>
                    · {{ $transaction->description }} · {{ $transaction->created_at->format('d/m H:i') }}
                </p>
            @empty
                <p class="mt-2 text-sm text-muted">Chưa có biến động.</p>
            @endforelse
        </section>
        <section>
            <h2 class="font-display text-xl">Nạp tiền</h2>
            @forelse ($member->deposits as $deposit)
                <p class="mt-2 text-sm">{{ $deposit->code }} · {{ $deposit->formattedAmount() }} · {{ $deposit->status->label() }}</p>
            @empty
                <p class="mt-2 text-sm text-muted">Chưa có lệnh nạp.</p>
            @endforelse
        </section>
        <section>
            <h2 class="font-display text-xl">Đơn hàng</h2>
            @forelse ($member->orders as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="mt-2 block text-sm hover:text-accent">{{ $order->code }} · {{ $order->formattedTotal() }}</a>
            @empty
                <p class="mt-2 text-sm text-muted">Chưa có đơn hàng.</p>
            @endforelse
        </section>
    </div>
</x-layouts.admin>
