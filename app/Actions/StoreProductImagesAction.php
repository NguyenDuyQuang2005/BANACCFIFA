<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

/**
 * Saves uploaded photos for an account listing. If the listing has no cover yet,
 * the first uploaded photo becomes the cover (or every new photo when $makeFirstCover).
 */
class StoreProductImagesAction
{
    /**
     * @param  list<UploadedFile>  $files
     */
    public function handle(Product $product, array $files, ?string $alt = null, bool $makeFirstCover = false): int
    {
        if ($files === []) {
            return 0;
        }

        $needsCover = $makeFirstCover || $product->images()->where('is_primary', true)->doesntExist();

        if ($needsCover) {
            $product->images()->update(['is_primary' => false]);
        }

        $sortOrder = (int) $product->images()->max('sort_order');

        foreach (array_values($files) as $index => $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'alt' => $alt ?: $product->name,
                'sort_order' => ++$sortOrder,
                'is_primary' => $needsCover && $index === 0,
            ]);
        }

        return count($files);
    }
}
