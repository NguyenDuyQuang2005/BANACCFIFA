<?php

namespace App\Models;

use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'account_name',
    'account_number',
    'bank_code',
    'qr_image_path',
    'is_active',
    'sort_order',
])]
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Deposit, $this>
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function qrUrl(): ?string
    {
        if ($this->qr_image_path === null) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_image_path);
    }
}
