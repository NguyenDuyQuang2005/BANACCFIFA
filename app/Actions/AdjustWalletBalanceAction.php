<?php

namespace App\Actions;

use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

/**
 * Manual balance correction by an admin. Always locked, always logged, reason required.
 */
class AdjustWalletBalanceAction
{
    public function __construct(
        private CreditWalletAction $creditWallet,
        private ActivityLogger $activityLogger,
    ) {}

    public function handle(User $member, WalletTransactionType $type, string $amount, string $reason, User $admin): WalletTransaction
    {
        return DB::transaction(function () use ($member, $type, $amount, $reason, $admin): WalletTransaction {
            $wallet = $member->wallet()->lockForUpdate()->firstOrFail();
            $balanceBefore = (string) $wallet->balance;

            $transaction = $this->creditWallet->handle(
                $wallet,
                $amount,
                $type,
                'Điều chỉnh bởi admin: '.$reason,
                $admin,
            );

            $this->activityLogger->log(
                'wallet.adjusted',
                ($type === WalletTransactionType::Credit ? 'Cộng ' : 'Trừ ').$amount.'₫ cho '.$member->email,
                $member,
                [
                    'type' => $type->value,
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => (string) $transaction->balance_after,
                    'reason' => $reason,
                ],
            );

            return $transaction;
        });
    }
}
