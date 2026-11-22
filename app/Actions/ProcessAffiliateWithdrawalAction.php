<?php

namespace App\Actions;

use App\Enums\AffiliateWithdrawalStatus;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Admin decision on a pending bank withdrawal. Rejecting returns the money to the commission balance.
 */
class ProcessAffiliateWithdrawalAction
{
    public function __construct(private ActivityLogger $activityLogger) {}

    public function handle(AffiliateWithdrawal $withdrawal, bool $approve, User $admin, ?string $note = null): AffiliateWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $approve, $admin, $note): AffiliateWithdrawal {
            $locked = AffiliateWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== AffiliateWithdrawalStatus::Pending) {
                throw new InvalidArgumentException('Yêu cầu rút này đã được xử lý.');
            }

            if (! $approve) {
                $member = User::query()->whereKey($locked->user_id)->lockForUpdate()->firstOrFail();
                $member->forceFill([
                    'commission_balance' => bcadd((string) $member->commission_balance, (string) $locked->amount, 2),
                ])->save();
            }

            $locked->update([
                'status' => $approve ? AffiliateWithdrawalStatus::Approved : AffiliateWithdrawalStatus::Rejected,
                'note' => $note,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            $this->activityLogger->log(
                $approve ? 'affiliate.withdrawal_approved' : 'affiliate.withdrawal_rejected',
                ($approve ? 'Duyệt' : 'Từ chối').' rút hoa hồng '.$locked->formattedAmount().' của '.$locked->user?->email,
                $locked,
                ['amount' => (string) $locked->amount, 'note' => $note],
            );

            return $locked;
        });
    }
}
