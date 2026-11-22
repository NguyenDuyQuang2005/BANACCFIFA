<?php

namespace App\Models;

use App\Enums\WalletTransactionType;
use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Number;

#[Fillable([
    'wallet_id',
    'type',
    'amount',
    'balance_after',
    'reference_type',
    'reference_id',
    'description',
])]
class WalletTransaction extends Model
{
    /** @use HasFactory<WalletTransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function formattedAmount(): string
    {
        return Number::format((float) $this->amount, locale: 'vi').'₫';
    }
}
