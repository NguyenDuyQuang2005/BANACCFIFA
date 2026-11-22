<?php

namespace App\Http\Controllers;

use App\Actions\RequestAffiliateWithdrawalAction;
use App\Enums\AffiliateWithdrawalStatus;
use App\Models\AffiliateWithdrawal;
use App\Support\AffiliateSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function show(Request $request, AffiliateSettings $settings): View
    {
        $user = $request->user();
        $withdrawals = $user->affiliateWithdrawals();

        return view('affiliate.show', [
            'user' => $user,
            'settings' => $settings->all(),
            'rate' => $user->affiliate_rate !== null ? (float) $user->affiliate_rate : $settings->rate(),
            'stats' => [
                'balance' => (float) $user->commission_balance,
                'withdrawn' => (float) (clone $withdrawals)->where('status', AffiliateWithdrawalStatus::Approved)->sum('amount'),
                'pending' => (float) (clone $withdrawals)->where('status', AffiliateWithdrawalStatus::Pending)->sum('amount'),
                'earned' => (float) $user->affiliateCommissions()->sum('amount'),
                'referrals' => $user->referrals()->count(),
                'clicks' => (int) $user->affiliate_clicks,
                'orders' => $user->affiliateCommissions()->count(),
                'sales' => (float) $user->affiliateCommissions()->sum('order_total'),
            ],
            'commissions' => $user->affiliateCommissions()->with('referredUser:id,name')->latest('id')->limit(10)->get(),
            'referrals' => $user->referrals()->latest('id')->limit(10)->get(['id', 'name', 'created_at']),
            'withdrawalHistory' => $user->affiliateWithdrawals()->latest('id')->paginate(10),
        ]);
    }

    public function withdraw(Request $request, AffiliateSettings $settings, RequestAffiliateWithdrawalAction $requestWithdrawal): RedirectResponse
    {
        abort_unless($settings->enabled(), 403);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:'.$settings->minWithdraw(), 'max:'.$settings->maxWithdraw()],
            'method' => ['required', Rule::in([AffiliateWithdrawal::METHOD_WALLET, AffiliateWithdrawal::METHOD_BANK])],
            'bank_name' => ['nullable', 'required_if:method,bank', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'required_if:method,bank', 'string', 'max:40', 'regex:/^[0-9\s]{6,40}$/'],
            'bank_account_name' => ['nullable', 'required_if:method,bank', 'string', 'max:120'],
        ], [
            'amount.min' => 'Số tiền rút tối thiểu là :min₫.',
            'amount.max' => 'Số tiền rút tối đa là :max₫.',
            'bank_account_number.regex' => 'Số tài khoản chỉ gồm chữ số.',
        ]);

        $withdrawal = $requestWithdrawal->handle($request->user(), [
            ...$validated,
            'amount' => number_format((int) $validated['amount'], 2, '.', ''),
            'bank_account_number' => isset($validated['bank_account_number']) ? preg_replace('/\s+/', '', $validated['bank_account_number']) : null,
        ]);

        return back()->with('status', $withdrawal->method === AffiliateWithdrawal::METHOD_WALLET
            ? 'Đã chuyển '.$withdrawal->formattedAmount().' hoa hồng vào ví.'
            : 'Đã gửi yêu cầu rút '.$withdrawal->formattedAmount().' về ngân hàng. Admin sẽ chuyển khoản và cập nhật trạng thái.');
    }
}
