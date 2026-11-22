<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Effective affiliate settings: values saved by admin in `settings`, else config/affiliate.php.
 */
class AffiliateSettings
{
    /**
     * @var array<string, string>
     */
    private const KEYS = [
        'enabled' => 'affiliate_enabled',
        'rate' => 'affiliate_rate',
        'min_withdraw' => 'affiliate_min_withdraw',
        'max_withdraw' => 'affiliate_max_withdraw',
        'cookie_days' => 'affiliate_cookie_days',
    ];

    public function __construct(private SiteSettings $siteSettings) {}

    public function enabled(): bool
    {
        return (bool) $this->value('enabled');
    }

    public function rate(): float
    {
        return (float) $this->value('rate');
    }

    public function minWithdraw(): int
    {
        return (int) $this->value('min_withdraw');
    }

    public function maxWithdraw(): int
    {
        return (int) $this->value('max_withdraw');
    }

    public function cookieDays(): int
    {
        return max(1, (int) $this->value('cookie_days'));
    }

    /**
     * @return array{enabled: bool, rate: float, min_withdraw: int, max_withdraw: int, cookie_days: int}
     */
    public function all(): array
    {
        return [
            'enabled' => $this->enabled(),
            'rate' => $this->rate(),
            'min_withdraw' => $this->minWithdraw(),
            'max_withdraw' => $this->maxWithdraw(),
            'cookie_days' => $this->cookieDays(),
        ];
    }

    /**
     * @param  array{enabled: bool, rate: float|int|string, min_withdraw: int|string, max_withdraw: int|string, cookie_days: int|string}  $values
     */
    public function save(array $values): void
    {
        foreach (self::KEYS as $name => $key) {
            $value = $name === 'enabled' ? ($values[$name] ? '1' : '0') : (string) $values[$name];
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(SiteSettings::CACHE_KEY);
    }

    private function value(string $name): mixed
    {
        $stored = $this->siteSettings->stored()[self::KEYS[$name]] ?? null;

        return $stored ?? config('affiliate.'.$name);
    }
}
