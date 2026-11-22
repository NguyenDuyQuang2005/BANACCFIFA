<?php

namespace App\Http\Controllers\Admin;

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

    public function store(Request $request): RedirectResponse
    {
        $product = Product::query()->create($this->validated($request));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Đã tạo tài khoản. Có thể thêm ảnh và thông tin đăng nhập.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categories' => Category::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
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

        return redirect()->route('admin.products.edit', $product)->with('status', 'Đã cập nhật tài khoản.');
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
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('products', 'code')->ignore($product)],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('products', 'slug')->ignore($product)],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:50000000'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'max:50000000'],
            'bp' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'squad' => ['nullable', 'string', 'max:40'],
            'ovr' => ['nullable', 'integer', 'min:1', 'max:200'],
            'featured_players_text' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['required', 'boolean'],
            'credentials' => ['nullable', 'string', 'max:2000'],
        ]);

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
            'original_price' => $validated['original_price'] ?? null,
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
