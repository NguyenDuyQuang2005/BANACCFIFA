<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BankTransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['review', ...array_column(BankTransactionStatus::cases(), 'value')])],
        ]);
        $status = $filters['status'] ?? null;

        return view('admin.bank-transactions.index', [
            'status' => $status,
            'statuses' => BankTransactionStatus::cases(),
            'reviewCount' => BankTransaction::query()
                ->whereIn('status', [BankTransactionStatus::AmountMismatch, BankTransactionStatus::DepositClosed, BankTransactionStatus::Unmatched])
                ->count(),
            'transactions' => BankTransaction::query()
                ->with('deposit.user')
                ->when($status === 'review', fn ($query) => $query->whereIn('status', [
                    BankTransactionStatus::AmountMismatch,
                    BankTransactionStatus::DepositClosed,
                    BankTransactionStatus::Unmatched,
                ]))
                ->when($status !== null && $status !== 'review', fn ($query) => $query->where('status', $status))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'webhookUrl' => route('payments.sepay'),
            'isConfigured' => filled(config('services.sepay.webhook_api_key')),
        ]);
    }
}
