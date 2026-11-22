<?php

namespace App\Models;

use App\Enums\BankTransactionStatus;
use Database\Factories\BankTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

#[Fillable([
    'provider',
    'provider_transaction_id',
    'gateway',
    'account_number',
    'amount',
    'content',
    'reference_code',
    'transacted_at',
    'status',
    'note',
    'deposit_id',
    'payload',
])]
class BankTransaction extends Model
{
    /** @use HasFactory<BankTransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transacted_at' => 'datetime',
            'status' => BankTransactionStatus::class,
            'payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Deposit, $this>
     */
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function formattedAmount(): string
    {
        return Number::format((float) $this->amount, locale: 'vi').'₫';
    }
}
