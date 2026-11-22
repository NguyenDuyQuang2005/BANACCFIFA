<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidVoucherException;
use App\Exceptions\ProductUnavailableException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Support\VoucherCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Buys one account: locks the listing, applies an optional voucher, debits the wallet,
 * records the order and pays affiliate commission — all in one transaction.
 */
class PurchaseProductAction
{
    public function __construct(
        private CreditWalletAction $creditWallet,
        private AwardAffiliateCommissionAction $awardAffiliateCommission,
        private VoucherCalculator $voucherCalculator,
    ) {}

    /**
     * @throws ProductUnavailableException
     * @throws InvalidVoucherException
     * @throws InsufficientBalanceException
     */
    public function handle(User $user, Product $product, ?string $voucherCode = null): Order
    {
        return DB::transaction(function () use ($user, $product, $voucherCode): Order {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($lockedProduct->status !== ProductStatus::Available) {
                throw new ProductUnavailableException;
            }

            $subtotal = (float) $lockedProduct->price;
            $voucher = null;
            $discount = 0;

            if (filled($voucherCode)) {
                $voucher = Voucher::query()
                    ->where('code', mb_strtoupper(trim($voucherCode)))
                    ->lockForUpdate()
                    ->first();

                if ($voucher === null) {
                    throw new InvalidVoucherException('Mã giảm giá không tồn tại.');
                }

                $discount = $this->voucherCalculator->discountFor($voucher, $user, $subtotal);
            }

            $total = number_format($subtotal - $discount, 2, '.', '');
            $wallet = $user->wallet()->lockForUpdate()->firstOrFail();

            $order = Order::query()->create([
                'user_id' => $user->id,
                'code' => $this->uniqueOrderCode(),
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'voucher_code' => $voucher?->code,
                'total' => $total,
                'status' => OrderStatus::Completed,
                'customer_phone_masked' => $user->maskedPhone(),
                'completed_at' => now(),
            ]);

            $order->items()->create([
                'product_id' => $lockedProduct->id,
                'product_code' => $lockedProduct->code,
                'product_name' => $lockedProduct->name,
                'price' => $lockedProduct->price,
                'credentials' => $lockedProduct->credentials,
            ]);

            if (bccomp($total, '0', 2) > 0) {
                $this->creditWallet->handle(
                    $wallet,
                    $total,
                    WalletTransactionType::Debit,
                    'Mua tài khoản '.$lockedProduct->code.($voucher ? ' (mã '.$voucher->code.')' : ''),
                    $order,
                );
            }

            if ($voucher !== null) {
                $voucher->increment('used_count');
                VoucherRedemption::query()->create([
                    'voucher_id' => $voucher->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                ]);
            }

            $lockedProduct->update([
                'status' => ProductStatus::Sold,
                'sold_at' => now(),
            ]);

            $this->awardAffiliateCommission->handle($order, $user);

            return $order->load('items');
        });
    }

    private function uniqueOrderCode(): string
    {
        do {
            $code = 'DH'.Str::upper(Str::random(8));
        } while (Order::query()->where('code', $code)->exists());

        return $code;
    }
}
