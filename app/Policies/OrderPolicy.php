<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Order $order): bool
    {
        if ($user && $user->is_admin) {
            return true;
        }

        if ($user && $order->user_id !== null && (int) $order->user_id === (int) $user->getKey()) {
            return true;
        }

        return session()->has('order_view_' . $order->id);
    }
}
