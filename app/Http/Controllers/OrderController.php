<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => $request->user()->orders()->with('items')->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load('items');

        return view('orders.show', [
            'order' => $order,
        ]);
    }
}
