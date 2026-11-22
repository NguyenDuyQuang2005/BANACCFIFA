<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-methods.index', [
            'methods' => PaymentMethod::query()->orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        unset($validated['qr_image']);
        $validated['qr_image_path'] = $this->storeQr($request);

        PaymentMethod::query()->create($validated);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Đã thêm phương thức nạp tiền.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', [
            'method' => $paymentMethod,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $this->validated($request);
        unset($validated['qr_image']);
        $path = $this->storeQr($request);

        if ($path !== null) {
            if ($paymentMethod->qr_image_path) {
                Storage::disk('public')->delete($paymentMethod->qr_image_path);
            }
            $validated['qr_image_path'] = $path;
        }

        $paymentMethod->update($validated);

        return redirect()->route('admin.payment-methods.index')->with('status', 'Đã cập nhật ngân hàng.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->deposits()->exists()) {
            $paymentMethod->update(['is_active' => false]);

            return back()->with('status', 'Phương thức đã có lệnh nạp nên chỉ bị tắt, không xóa.');
        }

        if ($paymentMethod->qr_image_path) {
            Storage::disk('public')->delete($paymentMethod->qr_image_path);
        }

        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')->with('status', 'Đã xóa phương thức nạp tiền.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'account_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:40'],
            'bank_code' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
            'qr_image' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp'],
        ]);
    }

    private function storeQr(Request $request): ?string
    {
        if (! $request->hasFile('qr_image')) {
            return null;
        }

        return $request->file('qr_image')->store('payments', 'public');
    }
}
