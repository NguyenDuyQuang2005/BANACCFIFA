<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogFeed
{
    /**
     * @return Collection<int, Category>
     */
    public function visibleCategories(): Collection
    {
        return Category::query()
            ->visible()
            ->withCount(['products as available_products_count' => fn (Builder $products) => $products->where('status', ProductStatus::Available)])
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function featuredProducts(): Collection
    {
        return Product::query()
            ->featured()
            ->whereHas('category', fn (Builder $category) => $category->where('is_visible', true))
            ->with(['category', 'coverImage', 'images'])
            ->latest('id')
            ->limit(9)
            ->get();
    }

    /**
     * @param  array{
     *     q?: string|null,
     *     category?: string|null,
     *     price_min?: mixed,
     *     price_max?: mixed,
     *     bp_min?: mixed,
     *     ovr_min?: mixed,
     *     status?: string|null,
     *     sort?: string|null
     * }  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginatedProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->public()
            ->whereHas('category', fn (Builder $category) => $category->where('is_visible', true))
            ->with(['category', 'coverImage', 'images']);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'] ?? 'newest');

        return $query->paginate(12)->withQueryString();
    }

    /**
     * @return Collection<int, Product>
     */
    public function relatedProducts(Product $product): Collection
    {
        return Product::query()
            ->available()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'coverImage', 'images'])
            ->latest('id')
            ->limit(4)
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentCompletedTransactions(): Collection
    {
        return Order::query()
            ->where('status', OrderStatus::Completed)
            ->with('items')
            ->latest('completed_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(function (Order $order): array {
                $item = $order->items->first();

                return [
                    'code' => $order->publicCode(),
                    'product_name' => $item?->product_name ?? 'Tài khoản FC Online',
                    'masked_phone' => $order->customer_phone_masked ?? '—',
                    'amount' => $order->formattedTotal(),
                    'status' => $order->status->label(),
                    'completed_at' => $order->completed_at?->diffForHumans() ?? '',
                ];
            });
    }

    public function memberCount(): ?int
    {
        $count = User::query()->count();

        return $count > 0 ? $count : null;
    }

    public function completedOrderCount(): ?int
    {
        $count = Order::query()->where('status', OrderStatus::Completed)->count();

        return $count > 0 ? $count : null;
    }

    /**
     * @param  Builder<Product>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query->search($filters['q'] ?? null);

        if (! empty($filters['category'])) {
            $query->whereHas('category', function (Builder $category) use ($filters): void {
                $category->where('slug', $filters['category'])->where('is_visible', true);
            });
        }

        if (isset($filters['price_min']) && $filters['price_min'] !== '' && $filters['price_min'] !== null) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max']) && $filters['price_max'] !== '' && $filters['price_max'] !== null) {
            $query->where('price', '<=', $filters['price_max']);
        }

        if (isset($filters['bp_min']) && $filters['bp_min'] !== '' && $filters['bp_min'] !== null) {
            $query->where('bp', '>=', $filters['bp_min']);
        }

        if (isset($filters['ovr_min']) && $filters['ovr_min'] !== '' && $filters['ovr_min'] !== null) {
            $query->where('ovr', '>=', $filters['ovr_min']);
        }

        if (($filters['status'] ?? '') === ProductStatus::Available->value) {
            $query->available();
        }

        if (($filters['status'] ?? '') === ProductStatus::Sold->value) {
            $query->where('status', ProductStatus::Sold);
        }
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->latest('id'),
        };
    }
}
