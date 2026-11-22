<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\HeroBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HeroBannerController extends Controller
{
    public function update(Request $request, HeroBanner $heroBanner, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'hero_media' => ['nullable', 'file', 'max:20480', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm'],
            'hero_link_url' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/'],
        ], [
            'hero_media.mimetypes' => 'Chỉ nhận ảnh (jpg, png, webp, gif) hoặc video (mp4, webm).',
            'hero_link_url.regex' => 'Link phải bắt đầu bằng http://, https:// hoặc / (đường dẫn nội bộ).',
        ]);

        if ($request->hasFile('hero_media')) {
            $file = $request->file('hero_media');
            $type = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image';
            $heroBanner->replace($file, $type);
            $activityLogger->log('hero.updated', 'Đổi banner đầu trang ('.($type === 'video' ? 'video' : 'ảnh').')');
        }

        $heroBanner->updateLink($validated['hero_link_url'] ?? null);

        return back()->with('status', 'Đã lưu banner đầu trang.');
    }

    public function destroy(HeroBanner $heroBanner, ActivityLogger $activityLogger): RedirectResponse
    {
        $heroBanner->remove();
        $activityLogger->log('hero.removed', 'Gỡ banner đầu trang');

        return back()->with('status', 'Đã gỡ banner đầu trang. Trang chủ quay về phần giới thiệu dạng chữ.');
    }
}
