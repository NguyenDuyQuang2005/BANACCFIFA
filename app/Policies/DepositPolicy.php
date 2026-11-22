<?php

namespace App\Policies;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;

class DepositPolicy
{
    public function view(User $user, Deposit $deposit): bool
    {
        return $deposit->user_id === $user->id;
    }

    public function cancel(User $user, Deposit $deposit): bool
    {
        return $deposit->user_id === $user->id && $deposit->status === DepositStatus::Pending;
    }
}
