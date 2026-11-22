<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Only the buyer may see an order, including the delivered game-account credentials.
     */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
