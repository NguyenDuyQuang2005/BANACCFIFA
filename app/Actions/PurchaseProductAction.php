<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\ProductUnavailableException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseProductAction
{
    public function __construct(
        private CreditWalletAction $creditWallet,
        private AwardAffiliateCommissionAction $awardAffiliateCommission,
    ) {}

    public function handle(User $user, Product $product): Order
    {
        return DB::transaction(function () use ($user, $product): Order {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($lockedProduct->status !== ProductStatus::Available) {
                throw new ProductUnavailableException;
            }

            $wallet = $user->wallet()->lockForUpdate()->firstOrFail();

            $order = Order::query()->create([
                'user_id' => $user->id,
                'code' => $this->uniqueOrderCode(),
                'total' => $lockedProduct->price,
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

            $this->creditWallet->handle(
                $wallet,
                (string) $lockedProduct->price,
                WalletTransactionType::Debit,
                'Mua tài khoản '.$lockedProduct->code,
                $order,
            );

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
