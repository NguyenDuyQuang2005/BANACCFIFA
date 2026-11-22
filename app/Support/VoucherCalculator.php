<?php

namespace App\Support;

use App\Exceptions\InvalidVoucherException;
use App\Models\User;
use App\Models\Voucher;

/**
 * Checks whether a voucher can be used by a member on an order of $subtotal and returns the discount (whole ₫).
 * Read-only; PurchaseProductAction re-runs it on a locked voucher row before recording the redemption.
 */
class VoucherCalculator
{
    public function findByCode(?string $code): ?Voucher
    {
        $code = mb_strtoupper(trim((string) $code));

        return $code === '' ? null : Voucher::query()->where('code', $code)->first();
    }

    /**
     * @throws InvalidVoucherException
     */
    public function discountFor(Voucher $voucher, User $user, float $subtotal): int
    {
        $now = now();
        $money = fn ($amount): string => number_format((float) $amount, 0, ',', '.').'₫';

        if (! $voucher->is_active || $voucher->trashed()) {
            throw new InvalidVoucherException('Mã giảm giá không còn hiệu lực.');
        }

        if ($voucher->starts_at !== null && $voucher->starts_at->isAfter($now)) {
            throw new InvalidVoucherException('Mã giảm giá chưa đến thời gian sử dụng.');
        }

        if ($voucher->ends_at !== null && $voucher->ends_at->isBefore($now)) {
            throw new InvalidVoucherException('Mã giảm giá đã hết hạn.');
        }

        if ($voucher->user_id !== null && $voucher->user_id !== $user->id) {
            throw new InvalidVoucherException('Mã giảm giá này không dành cho tài khoản của bạn.');
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            throw new InvalidVoucherException('Mã giảm giá đã hết lượt sử dụng.');
        }

        if ($voucher->per_user_limit !== null
            && $voucher->redemptions()->where('user_id', $user->id)->count() >= $voucher->per_user_limit) {
            throw new InvalidVoucherException('Bạn đã dùng hết lượt của mã giảm giá này.');
        }

        if ($voucher->min_order !== null && $subtotal < (float) $voucher->min_order) {
            throw new InvalidVoucherException('Đơn tối thiểu '.$money($voucher->min_order).' để dùng mã này.');
        }

        $discount = $voucher->type === Voucher::TYPE_PERCENT
            ? round($subtotal * (float) $voucher->value / 100)
            : (float) $voucher->value;

        if ($voucher->type === Voucher::TYPE_PERCENT && $voucher->max_discount !== null) {
            $discount = min($discount, (float) $voucher->max_discount);
        }

        return (int) max(0, min($discount, $subtotal));
    }
}
