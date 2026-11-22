<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp'],
            'alt' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $path = $request->file('image')->store('products', 'public');
        $isFirst = $product->images()->doesntExist();

        if ($request->boolean('is_primary') || $isFirst) {
            $product->images()->update(['is_primary' => false]);
        }

        $product->images()->create([
            'path' => $path,
            'alt' => $validated['alt'] ?? $product->name,
            'sort_order' => (int) $product->images()->max('sort_order') + 1,
            'is_primary' => $request->boolean('is_primary') || $isFirst,
        ]);

        return back()->with('status', 'Đã thêm ảnh.');
    }

    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($product->images()->where('is_primary', true)->doesntExist()) {
            $product->images()->oldest('id')->first()?->update(['is_primary' => true]);
        }

        return back()->with('status', 'Đã xóa ảnh.');
    }
}
