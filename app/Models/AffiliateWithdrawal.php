<?php

namespace App\Models;

use App\Enums\AffiliateWithdrawalStatus;
use Database\Factories\AffiliateWithdrawalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

#[Fillable([
    'user_id',
    'amount',
    'method',
    'bank_name',
    'bank_account_number',
    'bank_account_name',
    'status',
    'note',
    'processed_by',
    'processed_at',
])]
#[Hidden(['bank_account_number'])]
class AffiliateWithdrawal extends Model
{
    /** @use HasFactory<AffiliateWithdrawalFactory> */
    use HasFactory;

    public const METHOD_WALLET = 'wallet';

    public const METHOD_BANK = 'bank';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => AffiliateWithdrawalStatus::class,
            'bank_account_number' => 'encrypted',
            'processed_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function formattedAmount(): string
    {
        return Number::format((float) $this->amount, locale: 'vi').'₫';
    }

    public function methodLabel(): string
    {
        return $this->method === self::METHOD_BANK ? 'Ngân hàng' : 'Ví trên web';
    }

    /**
     * Account number with only the last 4 digits visible, for the member's own history.
     */
    public function maskedAccountNumber(): ?string
    {
        if (blank($this->bank_account_number)) {
            return null;
        }

        return str_repeat('*', max(0, mb_strlen($this->bank_account_number) - 4)).mb_substr($this->bank_account_number, -4);
    }
}
