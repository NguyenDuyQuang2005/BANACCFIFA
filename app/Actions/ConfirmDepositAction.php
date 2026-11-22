<?php

namespace App\Actions;

use App\Enums\DepositStatus;
use App\Enums\WalletTransactionType;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConfirmDepositAction
{
    public function __construct(private CreditWalletAction $creditWallet) {}

    public function handle(Deposit $deposit, ?User $confirmedBy = null, ?string $providerReference = null): Deposit
    {
        return DB::transaction(function () use ($deposit, $confirmedBy, $providerReference): Deposit {
            $locked = Deposit::query()->whereKey($deposit->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === DepositStatus::Success) {
                return $locked;
            }

            if (! in_array($locked->status, [DepositStatus::Pending, DepositStatus::Verifying], true)) {
                throw new InvalidArgumentException('Khoản nạp này không thể xác nhận.');
            }

            $wallet = $locked->user->wallet()->lockForUpdate()->firstOrFail();

            $this->creditWallet->handle(
                $wallet,
                (string) $locked->amount,
                WalletTransactionType::Credit,
                'Nạp tiền '.$locked->code,
                $locked,
            );

            $locked->update([
                'status' => DepositStatus::Success,
                'confirmed_at' => now(),
                'confirmed_by' => $confirmedBy?->id,
                'provider_reference' => $providerReference ?? $locked->provider_reference,
            ]);

            return $locked->refresh();
        });
    }
}
