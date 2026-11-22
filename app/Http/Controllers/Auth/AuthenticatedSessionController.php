<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        if ($request->user()?->isBanned()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $home = $request->user()?->isStaff()
            ? route('admin.dashboard')
            : route('account');

        return redirect()->intended($home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
