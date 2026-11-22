<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable site settings stored in the `settings` table and layered on
 * top of config('site.*') at boot, so existing views keep reading config().
 */
class SiteSettings
{
    public const CACHE_KEY = 'site_settings';

    /**
     * @var array<string, array{config: string, label: string, group: string, rules: list<string>}>
     */
    public const FIELDS = [
        'site_name' => ['config' => 'site.name', 'label' => 'Tên website', 'group' => 'Chung', 'rules' => ['required', 'string', 'max:60']],
        'site_tagline' => ['config' => 'site.tagline', 'label' => 'Khẩu hiệu', 'group' => 'Chung', 'rules' => ['nullable', 'string', 'max:120']],
        'site_description' => ['config' => 'site.description', 'label' => 'Mô tả mặc định (meta description)', 'group' => 'SEO', 'rules' => ['nullable', 'string', 'max:300']],
        'seo_og_image' => ['config' => 'site.seo.og_image', 'label' => 'Ảnh Open Graph mặc định (URL)', 'group' => 'SEO', 'rules' => ['nullable', 'url:http,https', 'max:255']],
        'seo_google_verification' => ['config' => 'site.seo.google_verification', 'label' => 'Mã xác minh Google Search Console', 'group' => 'SEO', 'rules' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-]*$/']],
        'social_zalo' => ['config' => 'site.social.zalo', 'label' => 'Link Zalo', 'group' => 'Mạng xã hội', 'rules' => ['nullable', 'url:http,https', 'max:255']],
        'social_telegram' => ['config' => 'site.social.telegram', 'label' => 'Link Telegram', 'group' => 'Mạng xã hội', 'rules' => ['nullable', 'url:http,https', 'max:255']],
        'social_facebook' => ['config' => 'site.social.facebook', 'label' => 'Link Facebook', 'group' => 'Mạng xã hội', 'rules' => ['nullable', 'url:http,https', 'max:255']],
        'social_tiktok' => ['config' => 'site.social.tiktok', 'label' => 'Link TikTok', 'group' => 'Mạng xã hội', 'rules' => ['nullable', 'url:http,https', 'max:255']],
        'services_notice' => ['config' => 'site.services.notice', 'label' => 'Dòng thông báo mục Dịch vụ FCO (bấm vào mở Zalo)', 'group' => 'Dịch vụ FCO', 'rules' => ['nullable', 'string', 'max:255']],
        'contact_email' => ['config' => 'site.contact.email', 'label' => 'Email liên hệ', 'group' => 'Liên hệ', 'rules' => ['nullable', 'email', 'max:255']],
        'contact_phone' => ['config' => 'site.contact.phone', 'label' => 'Số điện thoại liên hệ', 'group' => 'Liên hệ', 'rules' => ['nullable', 'string', 'max:30']],
    ];

    /**
     * @return array<string, string|null>
     */
    public function stored(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn (): array => Setting::query()->pluck('value', 'key')->all());
        } catch (QueryException) {
            return [];
        }
    }

    /**
     * Effective value of every editable field (database value, else .env/config).
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        return collect(self::FIELDS)
            ->mapWithKeys(fn (array $field, string $key): array => [$key => config($field['config'])])
            ->all();
    }

    public function applyToConfig(): void
    {
        foreach ($this->stored() as $key => $value) {
            if (isset(self::FIELDS[$key]) && $value !== null && $value !== '') {
                config([self::FIELDS[$key]['config'] => $value]);
            }
        }
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! isset(self::FIELDS[$key])) {
                continue;
            }

            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            config([self::FIELDS[$key]['config'] => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
