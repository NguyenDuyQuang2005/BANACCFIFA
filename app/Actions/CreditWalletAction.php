<?php

namespace App\Actions;

use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;

class CreditWalletAction
{
    public function handle(
        Wallet $wallet,
        string $amount,
        WalletTransactionType $type,
        string $description,
        ?Model $reference = null,
    ): WalletTransaction {
        $current = (string) $wallet->balance;
        $next = $type === WalletTransactionType::Credit
            ? bcadd($current, $amount, 2)
            : bcsub($current, $amount, 2);

        if ($type === WalletTransactionType::Debit && bccomp($next, '0', 2) < 0) {
            throw new InsufficientBalanceException;
        }

        $wallet->update(['balance' => $next]);

        return $wallet->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $next,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'description' => $description,
        ]);
    }
}
