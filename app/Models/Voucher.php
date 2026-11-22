<?php

namespace App\Models;

use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A discount code applied at checkout. "percent" takes value% of the price (capped by max_discount),
 * "fixed" takes a flat amount. Usage is counted in used_count and voucher_redemptions.
 */
#[Fillable([
    'code',
    'description',
    'type',
    'value',
    'max_discount',
    'min_order',
    'usage_limit',
    'per_user_limit',
    'user_id',
    'starts_at',
    'ends_at',
    'is_active',
])]
class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'min_order' => 'decimal:2',
            'usage_limit' => 'integer',
            'per_user_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<VoucherRedemption, $this>
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    /**
     * Short label such as "Giảm 50.000₫" or "Giảm 10% (tối đa 100.000₫)".
     */
    public function label(): string
    {
        $money = fn ($amount): string => number_format((float) $amount, 0, ',', '.').'₫';

        if ($this->type === self::TYPE_PERCENT) {
            return 'Giảm '.rtrim(rtrim(number_format((float) $this->value, 2, ',', '.'), '0'), ',').'%'
                .($this->max_discount !== null ? ' (tối đa '.$money($this->max_discount).')' : '');
        }

        return 'Giảm '.$money($this->value);
    }
}
