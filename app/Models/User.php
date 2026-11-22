<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'phone', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'banned_at' => 'datetime',
            'affiliate_rate' => 'decimal:2',
            'commission_balance' => 'decimal:2',
            'affiliate_clicks' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->affiliate_code ??= static::uniqueAffiliateCode();
        });

        static::created(function (User $user): void {
            $user->wallet()->create(['balance' => 0]);
        });
    }

    public static function uniqueAffiliateCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (static::query()->where('affiliate_code', $code)->exists());

        return $code;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    /**
     * @return HasMany<AffiliateCommission, $this>
     */
    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'referrer_id');
    }

    /**
     * @return HasMany<AffiliateWithdrawal, $this>
     */
    public function affiliateWithdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    public function affiliateLink(): string
    {
        return route('affiliate.track', $this->affiliate_code);
    }

    /**
     * @return HasOne<Wallet, $this>
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * @return HasMany<Deposit, $this>
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /**
     * Admin dev: full access, including system settings, bank accounts, balances and roles.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Admin quản lý: runs the shop day to day and sees revenue, but not system settings.
     */
    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isStaff(): bool
    {
        return $this->role->isStaff();
    }

    public function maskedPhone(): ?string
    {
        if ($this->phone === null || $this->phone === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone) ?? '';

        if (strlen($digits) < 6) {
            return '***';
        }

        return substr($digits, 0, 3).'***'.substr($digits, -3);
    }
}
