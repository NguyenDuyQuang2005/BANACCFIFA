<?php

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function index(Request $request): View
    {
        return view('deposits.index', [
            'wallet' => $request->user()->wallet()->firstOrFail(),
            'paymentMethods' => PaymentMethod::query()->active()->get(),
            'deposits' => $request->user()->deposits()->with('paymentMethod')->latest('id')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:10000', 'max:50000000'],
        ]);

        $method = PaymentMethod::query()->active()->whereKey($validated['payment_method_id'])->firstOrFail();

        $deposit = Deposit::query()->create([
            'user_id' => $request->user()->id,
            'payment_method_id' => $method->id,
            'code' => $this->uniqueDepositCode(),
            'amount' => $validated['amount'],
            'status' => DepositStatus::Pending,
        ]);

        return redirect()->route('deposits.show', $deposit)->with('status', 'Đã tạo lệnh nạp. Quét mã QR để chuyển khoản, số dư sẽ tự cộng khi ngân hàng báo tiền về.');
    }

    public function show(Request $request, Deposit $deposit): View
    {
        Gate::authorize('view', $deposit);

        $deposit->load('paymentMethod');

        return view('deposits.show', [
            'deposit' => $deposit,
            'wallet' => $request->user()->wallet()->firstOrFail(),
        ]);
    }

    /**
     * Polled by the deposit page so the member sees the balance update without reloading.
     */
    public function status(Request $request, Deposit $deposit): JsonResponse
    {
        Gate::authorize('view', $deposit);

        return response()->json([
            'status' => $deposit->status->value,
            'label' => $deposit->status->label(),
            'successful' => $deposit->isSuccessful(),
            'open' => $deposit->isOpen(),
            'balance' => $request->user()->wallet()->firstOrFail()->formattedBalance(),
        ]);
    }

    public function cancel(Request $request, Deposit $deposit): RedirectResponse
    {
        Gate::authorize('cancel', $deposit);

        $deposit->update(['status' => DepositStatus::Cancelled]);

        return redirect()->route('deposit')->with('status', 'Đã hủy lệnh nạp.');
    }

    /**
     * Fixed format "DT" + 8 characters so it can be found reliably inside bank transfer content.
     */
    private function uniqueDepositCode(): string
    {
        do {
            $code = 'DT'.Str::upper(Str::random(8));
        } while (Deposit::query()->where('code', $code)->exists());

        return $code;
    }
}
