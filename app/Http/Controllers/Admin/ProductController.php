<?php

namespace App\Http\Controllers\Admin;

use App\Actions\StoreProductImagesAction;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $products = Product::query()
            ->with('category')
            ->when(
                in_array($status, array_column(ProductStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status),
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, StoreProductImagesAction $storeImages): RedirectResponse
    {
        $product = Product::query()->create($this->validated($request));
        $imageCount = $storeImages->handle($product, $request->file('images', []));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Đã tạo tài khoản'.($imageCount > 0 ? " kèm {$imageCount} ảnh" : '').'.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categories' => Category::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Product $product, StoreProductImagesAction $storeImages): RedirectResponse
    {
        $validated = $this->validated($request, $product);

        if ($validated['status'] === ProductStatus::Available && $product->items()->exists()) {
            return back()->withErrors(['status' => 'Tài khoản đã gắn với đơn hàng, không mở bán lại.']);
        }

        if ($validated['status'] === ProductStatus::Sold) {
            $validated['sold_at'] = $product->sold_at ?? now();
        }

        if ($validated['status'] === ProductStatus::Available) {
            $validated['sold_at'] = null;
        }

        $product->update($validated);
        $imageCount = $storeImages->handle($product, $request->file('images', []));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Đã cập nhật tài khoản'.($imageCount > 0 ? " và thêm {$imageCount} ảnh" : '').'.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->items()->exists()) {
            return back()->withErrors(['product' => 'Không xóa tài khoản đã bán qua đơn hàng.']);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Đã xóa tài khoản.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        // Prices may be typed as "1.500.000" or "1 500 000"; keep only the digits.
        $request->merge(collect(['price', 'original_price', 'discount_percent', 'bp'])
            ->mapWithKeys(fn (string $field): array => [
                $field => filled($request->input($field)) ? preg_replace('/\D/', '', (string) $request->input($field)) : null,
            ])
            ->all());

        $validated = $request->validate([
            ...ProductImageController::rules(),
            'discount_percent' => ['nullable', 'integer', 'min:1', 'max:99'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('products', 'code')->ignore($product)],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('products', 'slug')->ignore($product)],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'required_without:discount_percent', 'integer', 'min:0', 'max:1000000000'],
            'original_price' => ['nullable', 'required_with:discount_percent', 'integer', 'min:0', 'max:1000000000'],
            'bp' => ['nullable', 'integer', 'min:0', 'max:1000000000000000'],
            'squad' => ['nullable', 'string', 'max:40'],
            'ovr' => ['nullable', 'integer', 'min:1', 'max:200'],
            'featured_players_text' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['required', 'boolean'],
            'credentials' => ['nullable', 'string', 'max:2000'],
        ], [
            'price.required_without' => 'Nhập giá bán, hoặc nhập giá gốc kèm % giảm giá.',
            'original_price.required_with' => 'Nhập giá gốc để tính giá sau giảm.',
            'images.max' => 'Tối đa '.ProductImageController::MAX_FILES.' ảnh mỗi lần lưu.',
        ]);

        $originalPrice = isset($validated['original_price']) ? (int) $validated['original_price'] : null;
        $price = isset($validated['discount_percent'])
            ? (int) round($originalPrice * (100 - (int) $validated['discount_percent']) / 100)
            : (int) $validated['price'];
        $validated['price'] = $price;
        $validated['original_price'] = $originalPrice !== null && $originalPrice > $price ? $originalPrice : null;

        $code = $validated['code'] ?? null;
        $code = $code ?: $this->uniqueCode();
        $slug = $validated['slug'] ?? null;
        $slug = $slug ?: Str::slug($validated['name']).'-'.Str::lower($code);

        return [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'code' => $code,
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'original_price' => $validated['original_price'],
            'bp' => $validated['bp'] ?? null,
            'squad' => $validated['squad'] ?? null,
            'ovr' => $validated['ovr'] ?? null,
            'featured_players' => $this->featuredPlayers($validated['featured_players_text'] ?? null),
            'status' => $validated['status'] instanceof ProductStatus
                ? $validated['status']
                : ProductStatus::from($validated['status']),
            'is_featured' => $validated['is_featured'],
            'credentials' => $validated['credentials'] ?? null,
        ];
    }

    private function uniqueCode(): string
    {
        do {
            $code = 'DT'.Str::upper(Str::random(6));
        } while (Product::query()->withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /**
     * @return list<string>
     */
    private function featuredPlayers(?string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $raw) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
