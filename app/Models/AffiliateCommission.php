<?php

namespace App\Models;

use Database\Factories\AffiliateCommissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

#[Fillable(['referrer_id', 'referred_user_id', 'order_id', 'order_total', 'rate', 'amount'])]
class AffiliateCommission extends Model
{
    /** @use HasFactory<AffiliateCommissionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_total' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function formattedAmount(): string
    {
        return Number::format((float) $this->amount, locale: 'vi').'₫';
    }
}
