<?php

namespace App\Actions;

use App\Models\AffiliateCommission;
use App\Models\Order;
use App\Models\User;
use App\Support\AffiliateSettings;

/**
 * Pays the referrer their % of a completed order. Must run inside the purchase transaction.
 */
class AwardAffiliateCommissionAction
{
    public function __construct(private AffiliateSettings $settings) {}

    public function handle(Order $order, User $buyer): ?AffiliateCommission
    {
        if (! $this->settings->enabled() || $buyer->referred_by_id === null || $buyer->referred_by_id === $buyer->id) {
            return null;
        }

        $referrer = User::query()->whereKey($buyer->referred_by_id)->lockForUpdate()->first();

        if ($referrer === null || $referrer->isBanned()) {
            return null;
        }

        $rate = $referrer->affiliate_rate !== null ? (string) $referrer->affiliate_rate : (string) $this->settings->rate();
        $amount = bcdiv(bcmul((string) $order->total, $rate, 4), '100', 0);

        if (bccomp($amount, '0', 2) <= 0) {
            return null;
        }

        $referrer->forceFill([
            'commission_balance' => bcadd((string) $referrer->commission_balance, $amount, 2),
        ])->save();

        return AffiliateCommission::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $buyer->id,
            'order_id' => $order->id,
            'order_total' => $order->total,
            'rate' => $rate,
            'amount' => $amount,
        ]);
    }
}
