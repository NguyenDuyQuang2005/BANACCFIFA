<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WalletTransactionType;
use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WalletTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::enum(WalletTransactionType::class)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = WalletTransaction::query()
            ->with('wallet.user')
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['q'] ?? null, fn ($query, string $search) => $query->whereHas(
                'wallet.user',
                fn ($query) => $query->where('email', 'like', '%'.$search.'%'),
            ));

        return view('admin.wallet-transactions.index', [
            'filters' => $filters,
            'totalCredit' => (clone $query)->where('type', WalletTransactionType::Credit)->sum('amount'),
            'totalDebit' => (clone $query)->where('type', WalletTransactionType::Debit)->sum('amount'),
            'transactions' => $query->latest('id')->paginate(30)->withQueryString(),
        ]);
    }
}
