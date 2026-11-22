<x-layouts.app title="Nạp tiền" robots="noindex, nofollow">
    <div class="mx-auto max-w-4xl px-4 py-10 lg:px-8">
        <h1 class="font-display text-3xl font-semibold">Nạp tiền</h1>
        <p class="mt-2 text-sm text-muted">Số dư hiện tại: <span class="text-accent">{{ $wallet->formattedBalance() }}</span>. Hệ thống không tự cộng tiền khi bạn chỉ gửi form.</p>
        @if (session('status'))
            <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
        @endif
        @error('balance') <x-alert type="danger" class="mt-6">{{ $message }}</x-alert> @enderror

        @if ($paymentMethods->isEmpty())
            <x-empty-state class="mt-8" title="Chưa cấu hình ngân hàng">
                Thêm phương thức thanh toán trong database (bảng payment_methods) hoặc trong Admin → Ngân hàng nạp tiền. Không bịa số tài khoản.
            </x-empty-state>
        @else
            <form method="post" action="{{ route('deposits.store') }}" class="mt-8 grid gap-4 rounded-3xl border border-line bg-surface p-6">
                @csrf
                <label class="grid gap-2 text-sm">
                    <span class="font-medium">Ngân hàng</span>
                    <select name="payment_method_id" class="rounded-xl border border-line bg-canvas px-3 py-2.5">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }} · {{ $method->account_number }}</option>
                        @endforeach
                    </select>
                </label>
                <x-form-input name="amount" type="number" label="Số tiền cần nạp" :value="old('amount')" min="10000" required />
                @error('amount') <p class="text-sm text-primary">{{ $message }}</p> @enderror
                <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Tạo lệnh nạp</button>
            </form>
        @endif

        <h2 class="mt-10 font-display text-xl font-semibold">Lịch sử nạp tiền</h2>
        <div class="mt-4 grid gap-3">
            @forelse ($deposits as $deposit)
                <a href="{{ route('deposits.show', $deposit) }}" class="rounded-2xl border border-line bg-surface p-4">
                    <p class="font-medium">{{ $deposit->code }}</p>
                    <p class="text-sm text-muted">{{ $deposit->formattedAmount() }} · {{ $deposit->status->label() }}</p>
                </a>
            @empty
                <p class="text-sm text-muted">Chưa có lệnh nạp.</p>
            @endforelse
        </div>
        <x-pagination :paginator="$deposits" />
    </div>
</x-layouts.app>
