<x-layouts.app title="Lệnh nạp {{ $deposit->code }}" robots="noindex, nofollow">
    <div class="mx-auto max-w-4xl px-4 py-10">
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Nạp tiền', 'url' => route('deposit')],
            ['label' => $deposit->code],
        ]" />
        <h1 class="mt-6 font-display text-3xl font-semibold">Chuyển khoản nạp tiền</h1>

        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif

        <div
            class="mt-6 grid gap-6 md:grid-cols-[minmax(0,300px)_1fr]"
            data-deposit
            data-status-url="{{ route('deposits.status', $deposit) }}"
            data-open="{{ $deposit->isOpen() ? '1' : '0' }}"
        >
            <section class="grid content-start justify-items-center gap-3 rounded-3xl border border-line bg-surface p-5 text-center">
                @if ($deposit->isOpen() && $deposit->qrUrl())
                    <img src="{{ $deposit->qrUrl() }}" alt="Mã QR chuyển khoản {{ $deposit->formattedAmount() }} nội dung {{ $deposit->code }}" class="w-full max-w-[260px] rounded-2xl bg-white p-2" data-deposit-qr>
                    <p class="text-xs text-muted">Mở app ngân hàng → Quét QR. Số tiền và nội dung đã điền sẵn.</p>
                @elseif ($deposit->isOpen())
                    <p class="py-10 text-sm text-muted">Ngân hàng này chưa có mã QR. Vui lòng chuyển khoản thủ công theo thông tin bên cạnh.</p>
                @endif

                <div class="w-full rounded-2xl border px-4 py-3 text-sm {{ $deposit->isSuccessful() ? 'border-emerald-500/40 bg-emerald-500/10' : 'border-line bg-canvas' }}" data-deposit-status-box>
                    <p class="text-xs uppercase tracking-widest text-muted">Trạng thái</p>
                    <p class="mt-1 flex items-center justify-center gap-2 font-semibold" data-deposit-status>
                        @if ($deposit->isOpen())
                            <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-accent" aria-hidden="true"></span>
                        @endif
                        {{ $deposit->status->label() }}
                    </p>
                    @if ($deposit->isOpen())
                        <p class="mt-1 text-xs text-muted" data-deposit-hint>Đang chờ tiền về, trang tự cập nhật…</p>
                    @endif
                </div>
            </section>

            <section class="grid content-start gap-3 rounded-3xl border border-line bg-surface p-6 text-sm">
                @foreach ([
                    ['Ngân hàng', $deposit->paymentMethod->name, null],
                    ['Chủ tài khoản', $deposit->paymentMethod->account_name, null],
                    ['Số tài khoản', $deposit->paymentMethod->account_number, $deposit->paymentMethod->account_number],
                    ['Số tiền', $deposit->formattedAmount(), (string) (int) $deposit->amount],
                    ['Nội dung chuyển khoản', $deposit->code, $deposit->code],
                ] as [$label, $value, $copyValue])
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-3 last:border-0">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="flex items-center gap-2">
                            <strong class="{{ in_array($label, ['Số tiền', 'Nội dung chuyển khoản'], true) ? 'text-lg text-accent' : 'text-ink' }}">{{ $value }}</strong>
                            @if ($copyValue)
                                <button type="button" class="rounded-lg border border-line px-2 py-1 text-xs text-muted hover:border-accent hover:text-accent" data-copy="{{ $copyValue }}">Sao chép</button>
                            @endif
                        </span>
                    </div>
                @endforeach

                <x-alert type="warning" class="mt-2">
                    Ghi <strong>đúng nội dung {{ $deposit->code }}</strong> và <strong>đúng số tiền</strong>. Hệ thống tự cộng số dư khi ngân hàng báo tiền về (thường dưới 1 phút).
                    Sai nội dung hoặc sai số tiền sẽ chuyển sang chờ admin kiểm tra.
                </x-alert>

                <p class="text-muted">Số dư hiện tại: <strong class="text-ink" data-wallet-balance>{{ $wallet->formattedBalance() }}</strong></p>

                @if ($deposit->status === \App\Enums\DepositStatus::Pending)
                    <form method="post" action="{{ route('deposits.cancel', $deposit) }}" onsubmit="return confirm('Hủy lệnh nạp này? Nếu đã chuyển khoản, đừng hủy.')">
                        @csrf
                        <button class="text-sm text-muted hover:text-primary">Hủy lệnh nạp</button>
                    </form>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
