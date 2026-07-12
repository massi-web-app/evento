<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Modules\Orders\Contracts\PaidOrderReader;
use Modules\Orders\DTOs\PaidOrderItemSnapshot;
use Modules\Orders\DTOs\PaidOrderSnapshot;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Exceptions\OrderNotPaidException;
use Modules\Orders\Models\Order;

final class DatabasePaidOrderReader implements PaidOrderReader
{

    public function byPublicId(string $orderPublicId): PaidOrderSnapshot
    {
        /** @var Order|null $order */
        $order = Order::query()
            ->where('public_id', $orderPublicId)
            ->with('items')
            ->first();

        if ($order === null || $order->status !== OrderStatus::Paid) {
            throw OrderNotPaidException::forPublicId($orderPublicId);
        }

        return new PaidOrderSnapshot(
            userId: $order->user_id,
            eventId: $order->event_id,
            sessionId: $order->session_id,
            items: $order->items->map(fn ($i) => new PaidOrderItemSnapshot(
                orderItemId: $i->id,
                ticketTypeId: $i->ticket_type_id,
                quantity: $i->quantity,
            ))->all(),
        );
    }
}
