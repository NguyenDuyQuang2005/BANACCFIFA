<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DepositStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Support\RevenueReport;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $completedOrders = Order::query()->where('status', OrderStatus::Completed);
        $successfulDeposits = Deposit::query()->where('status', DepositStatus::Success);

        return view('admin.dashboard', [
            'memberCount' => User::query()->count(),
            'newMembersToday' => User::query()->whereDate('created_at', today())->count(),
            'availableCount' => Product::query()->where('status', ProductStatus::Available)->count(),
            'soldCount' => Product::query()->where('status', ProductStatus::Sold)->count(),
            'pendingDepositCount' => Deposit::query()->whereIn('status', [DepositStatus::Pending, DepositStatus::Verifying])->count(),
            'orderCount' => (clone $completedOrders)->count(),
            'revenueTotal' => (clone $completedOrders)->sum('total'),
            'revenueToday' => (clone $completedOrders)->whereDate('created_at', today())->sum('total'),
            'depositTotal' => (clone $successfulDeposits)->sum('amount'),
            'depositToday' => (clone $successfulDeposits)->whereDate('confirmed_at', today())->sum('amount'),
            'walletBalanceTotal' => Wallet::query()->sum('balance'),
            'openContactCount' => ContactMessage::query()->whereNull('handled_at')->count(),
            'recentOrders' => Order::query()->with('user')->latest('id')->limit(5)->get(),
            'recentActivity' => Gate::allows('manage-system')
                ? ActivityLog::query()->with('user')->latest('id')->limit(8)->get()
                : collect(),
            'revenueSeries' => (new RevenueReport('30d'))->series(),
        ]);
    }
}
