<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Illuminate\Support\Collection;
use Modules\Orders\Contracts\CapacityCounter;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;
use Modules\Shared\Contracts\Clock;

final readonly class HoldExpiryService
{
    public function __construct(
        private OrderTransitionService $transition,
        private CapacityCounter $capacity,
        private HoldService $holdService,
        private Clock $clock,
    ) {}

    public function expire(Order $order): bool
    {
        if (! $order->status->holdsCapacity() || ! $order->isHoldExpired()) {
            return false;
        }

        $this->transition->transition($order, OrderStatus::Expired, reason: 'hold_window_elapsed');

        foreach ($order->items as $item) {
            $this->capacity->release(
                $this->holdService->counterKey($item->ticket_type_id),
                $item->quantity,
            );
        }

        return true;
    }

    public function expireOverdue(int $batchSize = 200): int
    {
        $expired = 0;

        Order::query()
            ->whereIn('status', [OrderStatus::Pending, OrderStatus::AwaitingPayment])
            ->where('hold_expires_at', '<=', $this->clock->now())
            ->with('items')
            ->orderBy('id')
            ->chunkById($batchSize, function (Collection $orders) use (&$expired): void {
                foreach ($orders as $order) {
                    if ($this->expire($order)) {
                        $expired++;
                    }
                }
            });

        return $expired;
    }


}
