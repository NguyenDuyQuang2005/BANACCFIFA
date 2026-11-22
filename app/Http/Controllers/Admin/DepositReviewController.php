<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ConfirmDepositAction;
use App\Enums\DepositStatus;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DepositReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.deposits.index', [
            'deposits' => Deposit::query()
                ->with(['user', 'paymentMethod'])
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function confirm(Request $request, Deposit $deposit, ConfirmDepositAction $confirm, ActivityLogger $activityLogger): RedirectResponse
    {
        $wasSuccessful = $deposit->status === DepositStatus::Success;

        try {
            $confirmed = $confirm->handle($deposit, $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['deposit' => $exception->getMessage()]);
        }

        if (! $wasSuccessful) {
            $activityLogger->log('deposit.confirmed', 'Duyệt nạp '.$confirmed->code.' · '.$confirmed->formattedAmount(), $confirmed);
        }

        return back()->with('status', 'Đã xác nhận nạp tiền và cộng số dư.');
    }

    public function reject(Deposit $deposit, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_if($deposit->status === DepositStatus::Success, 403);

        $deposit->update(['status' => DepositStatus::Failed]);
        $activityLogger->log('deposit.rejected', 'Từ chối nạp '.$deposit->code, $deposit);

        return back()->with('status', 'Đã đánh dấu lệnh nạp thất bại. Số dư không thay đổi.');
    }
}
