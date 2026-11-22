<?php

namespace App\Support\Tools;

/**
 * FC Online transfer-market tax: tax = price × rate%.
 * The account perk (VIP/SVIP) and the coupon each refund their % of that tax.
 */
class TaxCalculator
{
    /**
     * @return array{price: int, tax: int, perk_refund: int, coupon_refund: int, net: int}
     */
    public function calculate(int $price, float $perkPercent, float $couponPercent, ?float $taxRate = null): array
    {
        $price = max(0, $price);
        $taxRate ??= (float) config('tools.tax.rate', 40);

        $tax = (int) round($price * $taxRate / 100);
        $perkRefund = (int) round($tax * $perkPercent / 100);
        $couponRefund = (int) round($tax * $couponPercent / 100);

        return [
            'price' => $price,
            'tax' => $tax,
            'perk_refund' => $perkRefund,
            'coupon_refund' => $couponRefund,
            'net' => $price - $tax + $perkRefund + $couponRefund,
        ];
    }
}
