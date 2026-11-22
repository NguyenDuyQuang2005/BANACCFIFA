<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\View\View;

class TransactionHistoryController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('status', OrderStatus::Completed)
            ->with('items')
            ->latest('completed_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('transactions.index', [
            'orders' => $orders,
        ]);
    }
}
