<?php

namespace App\Console\Commands;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Support\ActivityLogger;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Removes account listings sold more than N days ago: deletes their photos, wipes the
 * stored game credentials and soft-deletes the listing. Orders keep their own copy,
 * so buyers and revenue history are unaffected.
 */
#[Signature('products:prune-sold {--days= : Override the retention period (default: config site.sold_product_retention_days)} {--dry-run : Only list what would be removed}')]
#[Description('Xóa acc đã bán quá số ngày quy định (mặc định 30 ngày)')]
class PruneSoldProducts extends Command
{
    public function handle(ActivityLogger $activityLogger): int
    {
        $days = (int) ($this->option('days') ?: config('site.sold_product_retention_days', 30));
        $cutoff = now()->subDays(max(1, $days));

        $query = Product::query()
            ->where('status', ProductStatus::Sold)
            ->whereNotNull('sold_at')
            ->where('sold_at', '<=', $cutoff);

        if ($this->option('dry-run')) {
            $this->info('Sẽ xóa '.(clone $query)->count().' acc bán trước '.$cutoff->format('d/m/Y H:i').'.');

            return self::SUCCESS;
        }

        $removed = 0;

        $query->with('images')->chunkById(100, function ($products) use (&$removed): void {
            foreach ($products as $product) {
                DB::transaction(function () use ($product): void {
                    foreach ($product->images as $image) {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
                    }

                    $product->forceFill(['credentials' => null, 'is_featured' => false])->save();
                    $product->delete();
                });

                $removed++;
            }
        });

        if ($removed > 0) {
            $activityLogger->log('products.pruned_sold', "Tự xóa {$removed} acc đã bán quá {$days} ngày", properties: ['days' => $days, 'count' => $removed]);
        }

        $this->info("Đã xóa {$removed} acc đã bán quá {$days} ngày.");

        return self::SUCCESS;
    }
}
