<x-layouts.admin title="Giao dịch ngân hàng">
    <h1 class="font-display text-3xl font-semibold">Giao dịch ngân hàng (SePay)</h1>
    <p class="mt-2 text-sm text-muted">Mọi khoản tiền vào tài khoản do SePay báo về. Khớp đúng mã nạp và đúng số tiền sẽ được cộng tự động; các trường hợp còn lại cần admin xử lý ở trang Duyệt nạp tiền.</p>

    @unless ($isConfigured)
        <x-alert type="warning" class="mt-6">
            Chưa cấu hình <code>SEPAY_WEBHOOK_API_KEY</code> trong .env, nên webhook đang tắt (trả lỗi 503) và chưa có giao dịch nào được cộng tự động.
        </x-alert>
    @endunless

    @can('manage-system')
        <p class="mt-4 text-xs text-muted">URL webhook để khai báo trên SePay: <code class="text-accent">{{ $webhookUrl }}</code> · Kiểu chứng thực: API Key.</p>
    @endcan

    <nav class="mt-6 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('admin.bank-transactions.index') }}" class="rounded-full border px-4 py-2 {{ $status === null ? 'border-accent text-accent' : 'border-line text-muted' }}">Tất cả</a>
        <a href="{{ route('admin.bank-transactions.index', ['status' => 'review']) }}" class="rounded-full border px-4 py-2 {{ $status === 'review' ? 'border-accent text-accent' : 'border-line text-muted' }}">Cần xử lý ({{ $reviewCount }})</a>
        @foreach ($statuses as $item)
            <a href="{{ route('admin.bank-transactions.index', ['status' => $item->value]) }}" class="rounded-full border px-4 py-2 {{ $status === $item->value ? 'border-accent text-accent' : 'border-line text-muted' }}">{{ $item->label() }}</a>
        @endforeach
    </nav>

    <div class="mt-6 overflow-x-auto rounded-3xl border border-line bg-surface">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead class="text-xs uppercase tracking-widest text-muted">
                <tr>
                    <th class="px-4 py-3">Thời gian</th>
                    <th class="px-4 py-3">Số tiền</th>
                    <th class="px-4 py-3">Nội dung</th>
                    <th class="px-4 py-3">Kết quả</th>
                    <th class="px-4 py-3">Lệnh nạp</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr class="border-t border-line align-top">
                        <td class="whitespace-nowrap px-4 py-3 text-muted">
                            {{ $transaction->transacted_at?->format('d/m/Y H:i') }}
                            <span class="block text-xs">{{ $transaction->gateway }} · {{ $transaction->account_number }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 font-semibold">{{ $transaction->formattedAmount() }}</td>
                        <td class="max-w-xs break-words px-4 py-3">{{ $transaction->content }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs {{ $transaction->status === \App\Enums\BankTransactionStatus::Matched ? 'bg-emerald-500/15 text-emerald-400' : ($transaction->status->needsReview() ? 'bg-primary/15 text-primary' : 'bg-surface-2 text-muted') }}">
                                {{ $transaction->status->label() }}
                            </span>
                            @if ($transaction->note)
                                <span class="mt-1 block text-xs text-muted">{{ $transaction->note }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($transaction->deposit)
                                <p>{{ $transaction->deposit->code }} · {{ $transaction->deposit->formattedAmount() }}</p>
                                <p class="text-xs text-muted">{{ $transaction->deposit->user?->email }} · {{ $transaction->deposit->status->label() }}</p>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-muted">Chưa có giao dịch ngân hàng.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$transactions" />
</x-layouts.admin>
