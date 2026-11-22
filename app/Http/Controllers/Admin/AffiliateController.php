<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ProcessAffiliateWithdrawalAction;
use App\Enums\AffiliateWithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawal;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\AffiliateSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class AffiliateController extends Controller
{
    public function index(Request $request, AffiliateSettings $settings): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.affiliates.index', [
            'settings' => $settings->all(),
            'search' => $search,
            'totals' => [
                'commissions' => (float) AffiliateCommission::query()->sum('amount'),
                'outstanding' => (float) User::query()->sum('commission_balance'),
                'paid' => (float) AffiliateWithdrawal::query()->where('status', AffiliateWithdrawalStatus::Approved)->sum('amount'),
                'pending_count' => AffiliateWithdrawal::query()->where('status', AffiliateWithdrawalStatus::Pending)->count(),
                'referred_users' => User::query()->whereNotNull('referred_by_id')->count(),
            ],
            'affiliates' => User::query()
                ->withCount('referrals')
                ->withSum('affiliateCommissions as commissions_total', 'amount')
                ->withCount('affiliateCommissions as commissions_count')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('email', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('affiliate_code', mb_strtoupper($search));
                }))
                ->when($search === '', fn ($query) => $query->where(fn ($query) => $query->has('referrals')->orWhere('affiliate_clicks', '>', 0)->orWhere('commission_balance', '>', 0)))
                ->orderByDesc('commissions_total')
                ->orderByDesc('referrals_count')
                ->paginate(20)
                ->withQueryString(),
            'recentCommissions' => AffiliateCommission::query()->with(['referrer:id,email', 'referredUser:id,email', 'order:id,code'])->latest('id')->limit(10)->get(),
        ]);
    }

    public function updateSettings(Request $request, AffiliateSettings $settings, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'rate' => ['required', 'numeric', 'min:0', 'max:50'],
            'min_withdraw' => ['required', 'integer', 'min:1000'],
            'max_withdraw' => ['required', 'integer', 'gte:min_withdraw', 'max:1000000000'],
            'cookie_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $settings->save([...$validated, 'enabled' => (bool) $validated['enabled']]);
        $activityLogger->log('affiliate.settings_updated', 'Cập nhật cấu hình affiliate', properties: $validated);

        return back()->with('status', 'Đã lưu cấu hình affiliate.');
    }

    public function updateMember(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'affiliate_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'affiliate_code' => ['required', 'string', 'alpha_num', 'min:4', 'max:16', Rule::unique('users', 'affiliate_code')->ignore($user)],
        ]);

        $before = ['affiliate_rate' => $user->affiliate_rate, 'affiliate_code' => $user->affiliate_code];

        $user->forceFill([
            'affiliate_rate' => $validated['affiliate_rate'] ?? null,
            'affiliate_code' => mb_strtoupper($validated['affiliate_code']),
        ])->save();

        $activityLogger->log('affiliate.member_updated', 'Sửa affiliate của '.$user->email, $user, [
            'before' => $before,
            'after' => ['affiliate_rate' => $user->affiliate_rate, 'affiliate_code' => $user->affiliate_code],
        ]);

        return back()->with('status', 'Đã cập nhật affiliate của '.$user->email.'.');
    }

    public function withdrawals(Request $request): View
    {
        $status = $request->query('status', AffiliateWithdrawalStatus::Pending->value);

        return view('admin.affiliates.withdrawals', [
            'status' => $status,
            'withdrawals' => AffiliateWithdrawal::query()
                ->with(['user:id,name,email', 'processor:id,email'])
                ->when($status !== 'all', fn ($query) => $query->where('status', $status))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function processWithdrawal(Request $request, AffiliateWithdrawal $withdrawal, ProcessAffiliateWithdrawalAction $process): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $process->handle($withdrawal, $validated['decision'] === 'approve', $request->user(), $validated['note'] ?? null);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['withdrawal' => $exception->getMessage()]);
        }

        return back()->with('status', $validated['decision'] === 'approve'
            ? 'Đã đánh dấu đã chi trả.'
            : 'Đã từ chối và hoàn tiền về số dư hoa hồng của thành viên.');
    }
}
