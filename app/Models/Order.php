<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;

#[Fillable([
    'user_id',
    'code',
    'subtotal',
    'discount_total',
    'voucher_code',
    'total',
    'status',
    'customer_phone_masked',
    'completed_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    #[Scope]
    protected function completed(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Completed)->latest('completed_at')->orderByDesc('id');
    }

    public function formattedTotal(): string
    {
        return Number::format((float) $this->total, locale: 'vi').'₫';
    }

    public function publicCode(): string
    {
        $code = $this->code;

        if (strlen($code) <= 4) {
            return '****';
        }

        return substr($code, 0, 2).'****'.substr($code, -2);
    }
}
