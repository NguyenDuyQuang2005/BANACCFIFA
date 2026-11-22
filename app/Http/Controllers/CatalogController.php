<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\CatalogFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\CatalogFeed;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private CatalogFeed $catalogFeed) {}

    public function index(CatalogFilterRequest $request): View
    {
        return $this->listing($request);
    }

    public function category(CatalogFilterRequest $request, Category $category): View
    {
        abort_unless($category->is_visible, 404);

        return $this->listing($request, $category);
    }

    public function show(Product $product): View
    {
        abort_if($product->status === ProductStatus::Hidden, 404);
        abort_unless($product->category?->is_visible, 404);

        $product->load(['category', 'images']);

        return view('catalog.show', [
            'product' => $product,
            'relatedProducts' => $this->catalogFeed->relatedProducts($product),
        ]);
    }

    private function listing(CatalogFilterRequest $request, ?Category $category = null): View
    {
        $filters = $request->validated();
        $filters['sort'] ??= 'newest';

        if ($category !== null) {
            $filters['category'] = $category->slug;
        }

        $products = $this->catalogFeed->paginatedProducts($filters);

        return view('catalog.index', [
            'currentCategory' => $category,
            'categories' => $this->catalogFeed->visibleCategories(),
            'products' => $products,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
                'price_min' => $filters['price_min'] ?? '',
                'price_max' => $filters['price_max'] ?? '',
                'bp_min' => $filters['bp_min'] ?? '',
                'ovr_min' => $filters['ovr_min'] ?? '',
                'status' => $filters['status'] ?? '',
                'sort' => $filters['sort'],
            ],
        ]);
    }
}
