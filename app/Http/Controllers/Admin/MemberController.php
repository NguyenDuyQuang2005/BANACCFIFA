<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AdjustWalletBalanceAction;
use App\Enums\UserRole;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.members.index', [
            'search' => $search,
            'members' => User::query()
                ->with('wallet')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('email', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                }))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function show(User $user): View
    {
        $user->load([
            'wallet',
            'deposits' => fn ($query) => $query->latest('id')->limit(20),
            'orders' => fn ($query) => $query->latest('id')->limit(20),
        ]);

        return view('admin.members.show', [
            'member' => $user,
            'walletTransactions' => $user->wallet?->transactions()->limit(20)->get() ?? collect(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function adjustBalance(Request $request, User $user, AdjustWalletBalanceAction $adjustBalance): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::enum(WalletTransactionType::class)],
            'amount' => ['required', 'numeric', 'min:1000', 'max:1000000000', 'decimal:0,2'],
            'reason' => ['required', 'string', 'min:5', 'max:200'],
        ]);

        try {
            $adjustBalance->handle(
                $user,
                WalletTransactionType::from($validated['type']),
                number_format((float) $validated['amount'], 2, '.', ''),
                $validated['reason'],
                $request->user(),
            );
        } catch (InsufficientBalanceException) {
            return back()->withErrors(['amount' => 'Số dư không đủ để trừ số tiền này.'])->withInput();
        }

        return back()->with('status', 'Đã điều chỉnh số dư và ghi nhật ký.');
    }

    public function updateRole(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'Không thể tự đổi quyền của chính mình.']);
        }

        $from = $user->role->value;
        $user->forceFill(['role' => $validated['role']])->save();

        $activityLogger->log('member.role_changed', 'Đổi quyền '.$user->email.' từ '.$from.' sang '.$validated['role'], $user, [
            'from' => $from,
            'to' => $validated['role'],
        ]);

        return back()->with('status', 'Đã cập nhật quyền thành viên.');
    }

    public function toggleBan(Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['ban' => 'Không thể tự khóa tài khoản của chính mình.']);
        }

        $willBan = ! $user->isBanned();
        $user->forceFill(['banned_at' => $willBan ? now() : null])->save();

        $activityLogger->log(
            $willBan ? 'member.banned' : 'member.unbanned',
            ($willBan ? 'Khóa' : 'Mở khóa').' tài khoản '.$user->email,
            $user,
        );

        return back()->with('status', $willBan ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
    }
}
