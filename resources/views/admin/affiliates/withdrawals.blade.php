<x-layouts.admin title="Rút hoa hồng">
    <a href="{{ route('admin.affiliates.index') }}" class="text-sm text-muted hover:text-accent">← Affiliate</a>
    <h1 class="mt-3 font-display text-3xl font-semibold">Yêu cầu rút hoa hồng</h1>
    <p class="mt-2 text-sm text-muted">Rút về ví được cộng tự động. Rút về ngân hàng: chuyển khoản cho thành viên rồi bấm "Đã chi trả". Từ chối sẽ hoàn tiền về số dư hoa hồng của thành viên.</p>

    <nav class="mt-6 flex flex-wrap gap-2 text-sm">
        @foreach (['pending' => 'Chờ duyệt', 'approved' => 'Đã chi trả', 'rejected' => 'Từ chối', 'all' => 'Tất cả'] as $key => $label)
            <a href="{{ route('admin.affiliates.withdrawals', ['status' => $key]) }}" class="rounded-full border px-4 py-2 {{ $status === $key ? 'border-accent text-accent' : 'border-line text-muted' }}">{{ $label }}</a>
        @endforeach
    </nav>

    <div class="mt-6 grid gap-3">
        @forelse ($withdrawals as $withdrawal)
            <article class="rounded-2xl border border-line bg-surface p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="text-sm">
                        <p class="font-display text-xl font-semibold text-accent">{{ $withdrawal->formattedAmount() }}</p>
                        <p class="mt-1">{{ $withdrawal->user?->name }} · {{ $withdrawal->user?->email }}</p>
                        <p class="mt-1 text-muted">{{ $withdrawal->created_at->format('d/m/Y H:i') }} · Rút về {{ $withdrawal->methodLabel() }} · {{ $withdrawal->status->label() }}</p>
                        @if ($withdrawal->method === 'bank')
                            <div class="mt-3 rounded-xl border border-line bg-canvas px-4 py-3">
                                <p>Ngân hàng: <strong>{{ $withdrawal->bank_name }}</strong></p>
                                <p>Số tài khoản: <strong>{{ $withdrawal->bank_account_number }}</strong>
                                    <button type="button" class="ml-2 rounded border border-line px-2 py-0.5 text-xs text-muted hover:text-accent" data-copy="{{ $withdrawal->bank_account_number }}">Sao chép</button>
                                </p>
                                <p>Chủ tài khoản: <strong>{{ $withdrawal->bank_account_name }}</strong></p>
                            </div>
                        @endif
                        @if ($withdrawal->processed_at && $withdrawal->method === 'bank')
                            <p class="mt-2 text-xs text-muted">Xử lý bởi {{ $withdrawal->processor?->email ?? 'hệ thống' }} lúc {{ $withdrawal->processed_at->format('d/m/Y H:i') }}@if ($withdrawal->note) · {{ $withdrawal->note }}@endif</p>
                        @endif
                    </div>

                    @if ($withdrawal->status === \App\Enums\AffiliateWithdrawalStatus::Pending)
                        <form method="post" action="{{ route('admin.affiliates.withdrawals.process', $withdrawal) }}" class="grid w-full max-w-xs gap-2">
                            @csrf
                            <input type="text" name="note" maxlength="255" placeholder="Ghi chú (mã giao dịch, lý do từ chối...)" class="rounded-xl border border-line bg-canvas px-3 py-2 text-sm">
                            <div class="flex gap-2">
                                <button name="decision" value="approve" class="flex-1 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-white" onclick="return confirm('Xác nhận đã chuyển khoản cho thành viên?')">Đã chi trả</button>
                                <button name="decision" value="reject" class="flex-1 rounded-full border border-line px-4 py-2 text-xs" onclick="return confirm('Từ chối và hoàn tiền về số dư hoa hồng?')">Từ chối</button>
                            </div>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <p class="text-sm text-muted">Không có yêu cầu.</p>
        @endforelse
    </div>
    <x-pagination :paginator="$withdrawals" />
</x-layouts.admin>
