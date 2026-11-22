<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\HeroBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(HeroBanner $heroBanner): View
    {
        return view('admin.banners.index', [
            'hero' => $heroBanner->current(),
            'heroLink' => $heroBanner->link(),
            'banners' => Banner::query()->orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('banners', 'public');
        }

        Banner::query()->create($validated);

        return redirect()->route('admin.banners.index')->with('status', 'Đã tạo banner.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', [
            'banner' => $banner,
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $oldPath = $banner->image_path;
            $validated['image_path'] = $request->file('image')->store('banners', 'public');

            if ($oldPath !== null) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $banner->update($validated);

        return redirect()->route('admin.banners.index')->with('status', 'Đã cập nhật banner.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image_path !== null) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Đã xóa banner.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/'],
            'image' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'link_url.regex' => 'Link phải bắt đầu bằng http://, https:// hoặc / (đường dẫn nội bộ).',
        ]);

        unset($validated['image']);

        return $validated;
    }
}
