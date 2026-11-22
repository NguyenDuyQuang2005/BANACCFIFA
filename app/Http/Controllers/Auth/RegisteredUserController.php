<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\AffiliateLinkController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AffiliateSettings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request, AffiliateSettings $affiliateSettings): View
    {
        return view('auth.register', [
            'affiliateEnabled' => $affiliateSettings->enabled(),
            'referralCode' => $request->cookie(AffiliateLinkController::COOKIE),
        ]);
    }

    public function store(Request $request, AffiliateSettings $affiliateSettings): RedirectResponse
    {
        $request->merge([
            'referral_code' => filled($request->input('referral_code')) ? mb_strtoupper(trim((string) $request->input('referral_code'))) : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:16', 'exists:users,affiliate_code'],
        ], [
            'referral_code.exists' => 'Mã giới thiệu không tồn tại.',
        ]);

        $referralCode = $validated['referral_code'] ?? $request->cookie(AffiliateLinkController::COOKIE);
        $referrer = $affiliateSettings->enabled() && filled($referralCode)
            ? User::query()->where('affiliate_code', $referralCode)->whereNull('banned_at')->first()
            : null;

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => UserRole::Member,
        ]);
        $user->referred_by_id = $referrer?->id;
        $user->save();

        Cookie::queue(Cookie::forget(AffiliateLinkController::COOKIE));

        event(new Registered($user));
        Auth::login($user);

        return redirect()->intended(route('account'));
    }
}
