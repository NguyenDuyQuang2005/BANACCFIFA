<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AffiliateSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Landing URL of a referral link: remembers the referrer in a cookie and sends the visitor to sign up.
 */
class AffiliateLinkController extends Controller
{
    public const COOKIE = 'dt_ref';

    public function __invoke(Request $request, string $code, AffiliateSettings $settings): RedirectResponse
    {
        $target = $request->user() ? route('home') : route('register');

        if (! $settings->enabled()) {
            return redirect($target);
        }

        $referrer = User::query()->where('affiliate_code', mb_strtoupper($code))->first();

        if ($referrer === null || $referrer->isBanned() || $referrer->is($request->user())) {
            return redirect($target);
        }

        $seenCookie = 'dt_ref_seen_'.$referrer->affiliate_code;

        if (! $request->cookies->has($seenCookie)) {
            $referrer->increment('affiliate_clicks');
            Cookie::queue($seenCookie, '1', 60 * 24);
        }

        Cookie::queue(self::COOKIE, $referrer->affiliate_code, 60 * 24 * $settings->cookieDays());

        return redirect($target);
    }
}
