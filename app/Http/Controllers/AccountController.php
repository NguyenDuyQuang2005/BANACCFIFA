<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $wallet = $user->wallet()->firstOrFail();

        return view('account.show', [
            'user' => $user,
            'wallet' => $wallet,
            'deposits' => $user->deposits()->with('paymentMethod')->latest('id')->limit(5)->get(),
            'orders' => $user->orders()->with('items')->latest('id')->limit(5)->get(),
            'walletTransactions' => $wallet->transactions()->limit(8)->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->update($validated);

        return back()->with('status', 'Đã cập nhật thông tin cá nhân.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('status', 'Đã đổi mật khẩu.');
    }
}
