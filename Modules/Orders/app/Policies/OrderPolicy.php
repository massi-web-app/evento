<?php
declare(strict_types=1);

namespace Modules\Orders\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Orders\Models\Order;

final class OrderPolicy
{
    public function pay(Authenticatable $user, Order $order): bool
    {
        return (int) $user->getAuthIdentifier() === $order->user_id;
    }

    public function view(Authenticatable $user, Order $order): bool
    {
        return (int) $user->getAuthIdentifier() === $order->user_id;
    }
}
