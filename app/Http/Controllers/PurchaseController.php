<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseProductAction;
use App\Enums\ProductStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidVoucherException;
use App\Exceptions\ProductUnavailableException;
use App\Models\Product;
use App\Support\VoucherCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function create(Request $request, Product $product): View|RedirectResponse
    {
        $this->ensureListed($product);

        if (! $product->isAvailable()) {
            return redirect()->route('catalog.show', $product)->withErrors([
                'product' => 'Tài khoản này đã bán hoặc không còn khả dụng.',
            ]);
        }

        return view('purchases.create', [
            'product' => $product->load('category'),
            'wallet' => $request->user()->wallet()->firstOrFail(),
        ]);
    }

    /**
     * Price breakdown for the buy popup, optionally with a voucher. Nothing is reserved or charged.
     */
    public function quote(Request $request, Product $product, VoucherCalculator $vouchers): JsonResponse
    {
        $this->ensureListed($product);

        $validated = $request->validate(['voucher' => ['nullable', 'string', 'max:40']]);
        $subtotal = (float) $product->price;
        $discount = 0;
        $voucherLabel = null;
        $voucherError = null;

        if (filled($validated['voucher'] ?? null)) {
            $voucher = $vouchers->findByCode($validated['voucher']);

            try {
                if ($voucher === null) {
                    throw new InvalidVoucherException('Mã giảm giá không tồn tại.');
                }

                $discount = $vouchers->discountFor($voucher, $request->user(), $subtotal);
                $voucherLabel = $voucher->code.' · '.$voucher->label();
            } catch (InvalidVoucherException $exception) {
                $voucherError = $exception->getMessage();
            }
        }

        $balance = (float) $request->user()->wallet()->firstOrFail()->balance;
        $total = $subtotal - $discount;

        return response()->json([
            'available' => $product->isAvailable(),
            'product' => [
                'code' => $product->code,
                'name' => $product->name,
                'image' => $product->coverUrl(),
            ],
            'subtotal' => $this->money($subtotal),
            'discount' => $this->money($discount),
            'has_discount' => $discount > 0,
            'total' => $this->money($total),
            'balance' => $this->money($balance),
            'enough_balance' => $balance >= $total,
            'shortfall' => $this->money(max(0, $total - $balance)),
            'voucher' => $voucherLabel,
            'voucher_error' => $voucherError,
            'deposit_url' => route('deposit'),
        ]);
    }

    public function store(Request $request, Product $product, PurchaseProductAction $purchase): JsonResponse|RedirectResponse
    {
        $this->ensureListed($product);

        $validated = $request->validate(['voucher' => ['nullable', 'string', 'max:40']]);

        try {
            $order = $purchase->handle($request->user(), $product, $validated['voucher'] ?? null);
        } catch (InsufficientBalanceException $exception) {
            return $this->failure($request, 'balance', $exception->getMessage(), route('deposit'));
        } catch (ProductUnavailableException $exception) {
            return $this->failure($request, 'unavailable', $exception->getMessage(), route('catalog.show', $product));
        } catch (InvalidVoucherException $exception) {
            return $this->failure($request, 'voucher', $exception->getMessage(), route('purchases.create', $product));
        }

        if (! $request->expectsJson()) {
            return redirect()->route('orders.show', $order)->with('status', 'Mua thành công. Thông tin tài khoản chỉ hiển thị cho bạn.');
        }

        // Credentials go only to the buyer, over their own authenticated request, and are never cached.
        return response()->json([
            'order' => [
                'code' => $order->code,
                'url' => route('orders.show', $order),
                'total' => $this->money((float) $order->total),
                'discount' => (float) $order->discount_total > 0 ? $this->money((float) $order->discount_total) : null,
            ],
            'items' => $order->items->map(fn ($item): array => [
                'code' => $item->product_code,
                'name' => $item->product_name,
                'credentials' => $item->credentials,
            ])->values(),
            'balance' => $request->user()->wallet()->firstOrFail()->formattedBalance(),
        ])->header('Cache-Control', 'no-store, private');
    }

    private function ensureListed(Product $product): void
    {
        abort_if($product->status === ProductStatus::Hidden, 404);
        abort_unless($product->category?->is_visible, 404);
    }

    private function failure(Request $request, string $reason, string $message, string $redirectTo): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['reason' => $reason, 'message' => $message, 'redirect' => $redirectTo], 422);
        }

        return redirect($redirectTo)->withErrors([$reason === 'voucher' ? 'voucher' : ($reason === 'balance' ? 'balance' : 'product') => $message]);
    }

    private function money(float $amount): string
    {
        return Number::format($amount, locale: 'vi').'₫';
    }
}
