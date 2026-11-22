<?php

namespace App\Actions;

use App\Enums\AffiliateWithdrawalStatus;
use App\Enums\WalletTransactionType;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Moves commission out of the member's commission balance.
 * Wallet: credited instantly. Bank: held as a pending request until an admin pays or rejects it.
 */
class RequestAffiliateWithdrawalAction
{
    public function __construct(private CreditWalletAction $creditWallet) {}

    /**
     * @param  array{amount: string, method: string, bank_name?: string|null, bank_account_number?: string|null, bank_account_name?: string|null}  $data
     */
    public function handle(User $user, array $data): AffiliateWithdrawal
    {
        return DB::transaction(function () use ($user, $data): AffiliateWithdrawal {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $remaining = bcsub((string) $locked->commission_balance, $data['amount'], 2);

            if (bccomp($remaining, '0', 2) < 0) {
                throw ValidationException::withMessages(['amount' => 'Số dư hoa hồng không đủ.']);
            }

            $locked->forceFill(['commission_balance' => $remaining])->save();

            $isWallet = $data['method'] === AffiliateWithdrawal::METHOD_WALLET;

            $withdrawal = AffiliateWithdrawal::query()->create([
                'user_id' => $locked->id,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'bank_name' => $isWallet ? null : $data['bank_name'],
                'bank_account_number' => $isWallet ? null : $data['bank_account_number'],
                'bank_account_name' => $isWallet ? null : $data['bank_account_name'],
                'status' => $isWallet ? AffiliateWithdrawalStatus::Approved : AffiliateWithdrawalStatus::Pending,
                'processed_at' => $isWallet ? now() : null,
            ]);

            if ($isWallet) {
                $this->creditWallet->handle(
                    $locked->wallet()->lockForUpdate()->firstOrFail(),
                    $data['amount'],
                    WalletTransactionType::Credit,
                    'Rút hoa hồng giới thiệu về ví',
                    $withdrawal,
                );
            }

            return $withdrawal;
        });
    }
}
