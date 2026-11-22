<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * The full-width image or video at the top of the home page, stored in the `settings` table.
 * When nothing is uploaded, the home page keeps its text hero.
 */
class HeroBanner
{
    private const TYPE_KEY = 'hero_media_type';

    private const PATH_KEY = 'hero_media_path';

    private const LINK_KEY = 'hero_link_url';

    /**
     * @return array{type: string, url: string, link: string|null}|null
     */
    public function current(): ?array
    {
        $stored = app(SiteSettings::class)->stored();
        $type = $stored[self::TYPE_KEY] ?? null;
        $path = $stored[self::PATH_KEY] ?? null;

        if (! in_array($type, ['image', 'video'], true) || blank($path)) {
            return null;
        }

        return [
            'type' => $type,
            'url' => Storage::disk('public')->url($path),
            'link' => filled($stored[self::LINK_KEY] ?? null) ? $stored[self::LINK_KEY] : null,
        ];
    }

    public function link(): ?string
    {
        return app(SiteSettings::class)->stored()[self::LINK_KEY] ?? null;
    }

    public function replace(UploadedFile $file, string $type): void
    {
        $this->deleteFile();

        $this->put(self::TYPE_KEY, $type);
        $this->put(self::PATH_KEY, $file->store('hero', 'public'));
    }

    public function updateLink(?string $link): void
    {
        $this->put(self::LINK_KEY, $link);
    }

    public function remove(): void
    {
        $this->deleteFile();
        $this->put(self::TYPE_KEY, null);
        $this->put(self::PATH_KEY, null);
    }

    private function deleteFile(): void
    {
        $path = app(SiteSettings::class)->stored()[self::PATH_KEY] ?? null;

        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function put(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(SiteSettings::CACHE_KEY);
    }
}
