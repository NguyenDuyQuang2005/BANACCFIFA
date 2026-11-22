<?php

namespace App\Actions;

use App\Enums\BankTransactionStatus;
use App\Enums\DepositStatus;
use App\Models\BankTransaction;
use App\Models\Deposit;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Records one incoming bank transfer and, when its content carries an open deposit code
 * with the exact amount, credits the member's wallet. Safe to call twice for the same transfer.
 */
class ProcessBankTransferAction
{
    public function __construct(private ConfirmDepositAction $confirmDeposit) {}

    /**
     * @param  array{provider: string, provider_transaction_id: string, gateway?: string|null, account_number?: string|null, amount: string, content?: string|null, reference_code?: string|null, transacted_at?: string|null, is_incoming: bool, hinted_code?: string|null, payload: array<string, mixed>}  $transfer
     */
    public function handle(array $transfer): BankTransaction
    {
        $existing = $this->find($transfer['provider'], $transfer['provider_transaction_id']);

        if ($existing !== null) {
            return $existing;
        }

        try {
            return DB::transaction(fn (): BankTransaction => $this->process($transfer));
        } catch (UniqueConstraintViolationException) {
            // A parallel delivery of the same webhook won the race; it has already been handled.
            return $this->find($transfer['provider'], $transfer['provider_transaction_id']);
        }
    }

    /**
     * Pulls every possible deposit code out of free-form bank transfer content.
     * Banks often add prefixes or glue words together, so look for "DT" + 8 characters inside each token.
     *
     * @return list<string>
     */
    public static function candidateCodes(?string $content, ?string $hintedCode = null): array
    {
        $candidates = [];
        $tokens = preg_split('/[^A-Z0-9]+/', mb_strtoupper((string) $content).' '.mb_strtoupper((string) $hintedCode)) ?: [];

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $candidates[] = $token;
            $offset = 0;

            while (($position = strpos($token, 'DT', $offset)) !== false) {
                $candidates[] = substr($token, $position, 10);
                $offset = $position + 2;
            }
        }

        return array_values(array_unique(array_filter($candidates, fn (string $code): bool => str_starts_with($code, 'DT') && strlen($code) >= 8)));
    }

    /**
     * @param  array<string, mixed>  $transfer
     */
    private function process(array $transfer): BankTransaction
    {
        $record = BankTransaction::query()->create([
            'provider' => $transfer['provider'],
            'provider_transaction_id' => $transfer['provider_transaction_id'],
            'gateway' => $transfer['gateway'] ?? null,
            'account_number' => $transfer['account_number'] ?? null,
            'amount' => $transfer['amount'],
            'content' => isset($transfer['content']) ? mb_substr((string) $transfer['content'], 0, 500) : null,
            'reference_code' => $transfer['reference_code'] ?? null,
            'transacted_at' => $transfer['transacted_at'] ?? now(),
            'status' => BankTransactionStatus::Ignored,
            'payload' => $transfer['payload'],
        ]);

        if (! $transfer['is_incoming']) {
            return $this->finish($record, BankTransactionStatus::Ignored, 'Giao dịch tiền ra.');
        }

        $codes = self::candidateCodes($transfer['content'] ?? null, $transfer['hinted_code'] ?? null);
        $deposit = $codes === []
            ? null
            : Deposit::query()->whereIn('code', $codes)->lockForUpdate()->first();

        if ($deposit === null) {
            return $this->finish($record, BankTransactionStatus::Unmatched, 'Không tìm thấy mã nạp trong nội dung chuyển khoản.');
        }

        $record->deposit()->associate($deposit);

        if (! in_array($deposit->status, [DepositStatus::Pending, DepositStatus::Verifying], true)) {
            return $this->finish($record, BankTransactionStatus::DepositClosed, 'Lệnh nạp đang ở trạng thái: '.$deposit->status->label().'.');
        }

        if (bccomp((string) $deposit->amount, (string) $transfer['amount'], 2) !== 0) {
            $deposit->update([
                'status' => DepositStatus::Verifying,
                'meta' => [
                    ...($deposit->meta ?? []),
                    'received_amount' => $transfer['amount'],
                    'bank_transaction_id' => $record->id,
                ],
            ]);

            return $this->finish($record, BankTransactionStatus::AmountMismatch, 'Nhận '.$transfer['amount'].'₫, lệnh nạp yêu cầu '.$deposit->amount.'₫.');
        }

        $this->confirmDeposit->handle($deposit, null, $transfer['provider'].':'.$transfer['provider_transaction_id']);

        return $this->finish($record, BankTransactionStatus::Matched, 'Đã cộng số dư tự động.');
    }

    private function finish(BankTransaction $record, BankTransactionStatus $status, string $note): BankTransaction
    {
        $record->fill(['status' => $status, 'note' => $note])->save();

        return $record;
    }

    private function find(string $provider, string $providerTransactionId): ?BankTransaction
    {
        return BankTransaction::query()
            ->where('provider', $provider)
            ->where('provider_transaction_id', $providerTransactionId)
            ->first();
    }
}
