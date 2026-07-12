<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Couchbase\BaseException;
use Modules\Orders\Enums\orderStatus;
use Modules\Orders\Events\OrderStatusChanged;
use Modules\Orders\Exceptions\IllegalOrderTransitionException;
use Modules\Orders\Models\Order;

final class OrderTransitionService
{

    public function transition(Order $order, OrderStatus $to, ?int $actorUserId = null, ?string $reason = null): Order
    {
        $from = $order->status;

        if (! $from->canTransitionTo($to)) {
            throw IllegalOrderTransitionException::between($from, $to);
        }

        $order->forceFill([
            'status' => $to,
            'paid_at' => $to === OrderStatus::Paid && $order->paid_at === null
                ? now()
                : $order->paid_at,
        ])->save();

        event(new OrderStatusChanged(
            orderPublicId: $order->public_id,
            from: $from->value,
            to: $to->value,
            actorUserId: $actorUserId,
            reason: $reason,
        ));

        return $order;
    }
}
