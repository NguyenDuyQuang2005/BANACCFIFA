<?php

namespace App\Models;

use App\Enums\DepositStatus;
use Database\Factories\DepositFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;

#[Fillable([
    'user_id',
    'payment_method_id',
    'code',
    'amount',
    'status',
    'provider_reference',
    'confirmed_at',
    'confirmed_by',
    'meta',
])]
class Deposit extends Model
{
    /** @use HasFactory<DepositFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => DepositStatus::class,
            'confirmed_at' => 'datetime',
            'meta' => 'array',
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
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function formattedAmount(): string
    {
        return Number::format((float) $this->amount, locale: 'vi').'₫';
    }

    public function isSuccessful(): bool
    {
        return $this->status === DepositStatus::Success;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [DepositStatus::Pending, DepositStatus::Verifying], true);
    }

    /**
     * VietQR image with bank, account, exact amount and this deposit's code pre-filled,
     * so the member only has to scan and confirm. Falls back to the bank's static QR image.
     */
    public function qrUrl(): ?string
    {
        $method = $this->paymentMethod;

        if ($method === null) {
            return null;
        }

        if (blank($method->bank_code)) {
            return $method->qrUrl();
        }

        return sprintf(
            'https://img.vietqr.io/image/%s-%s-%s.png?%s',
            rawurlencode(trim($method->bank_code)),
            rawurlencode(preg_replace('/\s+/', '', $method->account_number) ?? ''),
            rawurlencode((string) config('services.vietqr.template', 'compact2')),
            http_build_query([
                'amount' => (int) round((float) $this->amount),
                'addInfo' => $this->code,
                'accountName' => $method->account_name,
            ], '', '&', PHP_QUERY_RFC3986),
        );
    }
}
