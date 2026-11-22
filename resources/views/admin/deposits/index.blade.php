<x-layouts.admin title="Duyệt nạp tiền">
    <h1 class="font-display text-3xl font-semibold">Duyệt nạp tiền</h1>
    <p class="mt-2 text-sm text-muted">Cộng số dư một lần cho mỗi lệnh thành công.</p>
    @error('deposit') <x-alert type="danger" class="mt-6">{{ $message }}</x-alert> @enderror
    <div class="mt-8 grid gap-3">
        @forelse ($deposits as $deposit)
            <article class="rounded-2xl border border-line bg-surface p-4">
                <p class="font-medium">{{ $deposit->code }} · {{ $deposit->formattedAmount() }}</p>
                <p class="text-sm text-muted">{{ $deposit->user->email }} · {{ $deposit->paymentMethod?->name }} · {{ $deposit->status->label() }}</p>
                @if (isset($deposit->meta['received_amount']) && ! $deposit->isSuccessful())
                    <p class="mt-1 text-sm text-primary">
                        Ngân hàng báo nhận {{ number_format((float) $deposit->meta['received_amount'], 0, ',', '.') }}₫ (khác số tiền lệnh nạp).
                        Xác nhận sẽ cộng đúng số tiền của lệnh nạp; nếu cần cộng theo số thực nhận, từ chối lệnh này và điều chỉnh số dư ở trang Thành viên.
                    </p>
                @endif
                @if (in_array($deposit->status->value, ['pending', 'verifying'], true))
                    <div class="mt-3 flex gap-2">
                        <form method="post" action="{{ route('admin.deposits.confirm', $deposit) }}">
                            @csrf
                            <button class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-white">Xác nhận</button>
                        </form>
                        <form method="post" action="{{ route('admin.deposits.reject', $deposit) }}">
                            @csrf
                            <button class="rounded-full border border-line px-4 py-2 text-xs">Từ chối</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <p class="text-sm text-muted">Chưa có lệnh nạp.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$deposits" />
</x-layouts.admin>
