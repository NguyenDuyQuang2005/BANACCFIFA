<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseProductAction;
use App\Enums\ProductStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\ProductUnavailableException;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function create(Request $request, Product $product): View|RedirectResponse
    {
        abort_if($product->status === ProductStatus::Hidden, 404);
        abort_unless($product->category?->is_visible, 404);

        if (! $product->isAvailable()) {
            return redirect()->route('catalog.show', $product)->withErrors([
                'product' => 'Tài khoản này đã bán hoặc không còn khả dụng.',
            ]);
        }

        $wallet = $request->user()->wallet()->firstOrFail();

        return view('purchases.create', [
            'product' => $product->load('category'),
            'wallet' => $wallet,
        ]);
    }

    public function store(Request $request, Product $product, PurchaseProductAction $purchase): RedirectResponse
    {
        abort_if($product->status === ProductStatus::Hidden, 404);

        try {
            $order = $purchase->handle($request->user(), $product);
        } catch (InsufficientBalanceException $exception) {
            return redirect()->route('deposit')->withErrors(['balance' => $exception->getMessage()]);
        } catch (ProductUnavailableException $exception) {
            return redirect()->route('catalog.show', $product)->withErrors(['product' => $exception->getMessage()]);
        }

        return redirect()->route('orders.show', $order)->with('status', 'Mua thành công. Thông tin tài khoản chỉ hiển thị cho bạn.');
    }
}
