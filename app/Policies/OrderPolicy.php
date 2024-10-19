<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function delete(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin) {
            return true;
        }

        return $order->user_id == $user->id;
    }

    public function updateOrderStatus(User $user): bool
    {
        if ($user->isSuperAdmin) {
            return true;
        }

        return false;
    }
}
