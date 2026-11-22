<x-layouts.admin title="Giao dịch ví">
    <h1 class="font-display text-3xl font-semibold">Giao dịch ví</h1>
    <p class="mt-2 text-sm text-muted">Toàn bộ biến động số dư: nạp tiền, mua hàng, điều chỉnh của admin.</p>
    <form method="get" class="mt-6 flex max-w-2xl flex-wrap gap-2">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Email thành viên" class="min-w-0 flex-1 rounded-xl border border-line bg-canvas px-3 py-2.5 text-sm">
        <select name="type" class="rounded-xl border border-line bg-canvas px-3 py-2.5 text-sm">
            <option value="">Tất cả</option>
            <option value="credit" @selected(($filters['type'] ?? '') === 'credit')>Cộng tiền</option>
            <option value="debit" @selected(($filters['type'] ?? '') === 'debit')>Trừ tiền</option>
        </select>
        <button class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Lọc</button>
    </form>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <section class="rounded-3xl border border-line bg-surface p-5">
            <p class="text-xs uppercase tracking-widest text-muted">Tổng cộng vào (theo bộ lọc)</p>
            <p class="mt-2 font-display text-2xl text-emerald-400">{{ \Illuminate\Support\Number::format((float) $totalCredit, locale: 'vi') }}₫</p>
        </section>
        <section class="rounded-3xl border border-line bg-surface p-5">
            <p class="text-xs uppercase tracking-widest text-muted">Tổng trừ ra (theo bộ lọc)</p>
            <p class="mt-2 font-display text-2xl text-primary">{{ \Illuminate\Support\Number::format((float) $totalDebit, locale: 'vi') }}₫</p>
        </section>
    </div>
    <div class="mt-6 overflow-x-auto rounded-3xl border border-line bg-surface">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="text-xs uppercase tracking-widest text-muted">
                <tr>
                    <th class="px-4 py-3">Thời gian</th>
                    <th class="px-4 py-3">Thành viên</th>
                    <th class="px-4 py-3">Số tiền</th>
                    <th class="px-4 py-3">Số dư sau</th>
                    <th class="px-4 py-3">Nội dung</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    @php $isCredit = $transaction->type === \App\Enums\WalletTransactionType::Credit; @endphp
                    <tr class="border-t border-line">
                        <td class="whitespace-nowrap px-4 py-3 text-muted">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if ($transaction->wallet?->user)
                                <a href="{{ route('admin.members.show', $transaction->wallet->user) }}" class="hover:text-accent">{{ $transaction->wallet->user->email }}</a>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 {{ $isCredit ? 'text-emerald-400' : 'text-primary' }}">{{ $isCredit ? '+' : '−' }}{{ $transaction->formattedAmount() }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ \Illuminate\Support\Number::format((float) $transaction->balance_after, locale: 'vi') }}₫</td>
                        <td class="px-4 py-3 text-muted">{{ $transaction->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-muted">Chưa có giao dịch.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$transactions" />
</x-layouts.admin>
