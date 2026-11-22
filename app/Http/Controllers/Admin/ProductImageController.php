<?php

namespace App\Http\Controllers\Admin;

use App\Actions\StoreProductImagesAction;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public const MAX_FILES = 20;

    /**
     * @return array<string, list<string>>
     */
    public static function rules(bool $required = false): array
    {
        return [
            'images' => [$required ? 'required' : 'nullable', 'array', 'max:'.self::MAX_FILES],
            'images.*' => ['image', 'max:20480', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function store(Request $request, Product $product, StoreProductImagesAction $storeImages): RedirectResponse
    {
        $validated = $request->validate([
            ...self::rules(required: true),
            'alt' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['sometimes', 'boolean'],
        ], [
            'images.required' => 'Chọn ít nhất 1 ảnh.',
            'images.max' => 'Tối đa '.self::MAX_FILES.' ảnh mỗi lần tải.',
        ]);

        $count = $storeImages->handle($product, $request->file('images', []), $validated['alt'] ?? null, $request->boolean('is_primary'));

        return back()->with('status', "Đã thêm {$count} ảnh.");
    }

    public function makePrimary(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        ProductImage::query()->whereKey($image->id)->update(['is_primary' => true]);

        return back()->with('status', 'Đã đặt ảnh bìa.');
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
