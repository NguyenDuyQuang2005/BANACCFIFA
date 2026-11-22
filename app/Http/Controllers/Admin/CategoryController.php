<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('products')->orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Category::query()->create($validated);

        return redirect()->route('admin.categories.index')->with('status', 'Đã tạo danh mục.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Không xóa được danh mục đang có sản phẩm.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Đã xóa danh mục.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Category $category = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('categories', 'slug')->ignore($category)],
            'description' => ['nullable', 'string', 'max:500'],
            'notice' => ['nullable', 'string', 'max:255'],
            'notice_url' => ['nullable', 'url:http,https', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_visible' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = ($validated['slug'] ?? null) ?: Str::slug($validated['name']);

        $oldImagePath = $category?->image_path;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
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
