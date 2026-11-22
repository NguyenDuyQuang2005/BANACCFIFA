<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::query()->orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Service::query()->create($this->validated($request));

        return redirect()->route('admin.services.index')->with('status', 'Đã tạo dịch vụ.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request, $service));

        return redirect()->route('admin.services.index')->with('status', 'Đã cập nhật dịch vụ.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'Đã xóa dịch vụ.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Service $service = null): array
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:30', Rule::unique('services', 'code')->ignore($service)],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp'],
            'remove_image' => ['sometimes', 'boolean'],
            'contact_label' => ['nullable', 'string', 'max:60'],
            'contact_url' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_visible' => ['required', 'boolean'],
        ], [
            'contact_url.regex' => 'Link phải bắt đầu bằng http://, https:// hoặc / (đường dẫn nội bộ).',
        ]);

        $oldImagePath = $service?->image_path;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('services', 'public');
        } elseif ($request->boolean('remove_image')) {
            $validated['image_path'] = null;
        }

        if (array_key_exists('image_path', $validated) && $oldImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        unset($validated['image'], $validated['remove_image']);

        return $validated;
    }
}
