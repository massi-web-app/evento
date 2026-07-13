<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use Modules\Events\DTOs\SellableTicketType;
use Modules\Orders\Contracts\CapacityCounter;
use Modules\Orders\Events\OrderHeld;
use Modules\Orders\Exceptions\InsufficientCapacityException;
use Modules\Orders\Exceptions\InvalidQuantityException;
use Modules\Orders\Models\Order;
use Modules\Settings\Contracts\SettingsReader;
use Modules\Shared\Contracts\Clock;
use Modules\Shared\ValueObjects\Money;

final readonly class HoldService
{
    public function __construct(
        private CapacityCounter $capacity,
        private SettingsReader $settings,
        private Clock $clock,
    ) {}

    /**
     * رزرو ظرفیت + ساخت سفارش Pending با مهلت.
     *
     * @throws InvalidQuantityException
     * @throws InsufficientCapacityException
     */
    public function hold(int $userId, SellableTicketType $ticketType, int $quantity): Order
    {
        if ($quantity < $ticketType->minPerOrder || $quantity > $ticketType->maxPerOrder) {
            throw InvalidQuantityException::outOfBounds(
                $quantity, $ticketType->minPerOrder, $ticketType->maxPerOrder,
            );
        }

        $key = $this->counterKey($ticketType->id);
        $this->capacity->initializeIfMissing($key, $ticketType->remainingCapacity);

        if (! $this->capacity->tryAcquire($key, $quantity)) {
            throw InsufficientCapacityException::forTicketType($ticketType->publicId, $quantity);
        }

        try {
            $order = DB::transaction(function () use ($userId, $ticketType, $quantity): Order {
                $lineTotal = Money::of(
                    $ticketType->currentPrice->amount * $quantity,
                    $ticketType->currentPrice->currency,
                );

                $order = new Order([
                    'user_id' => $userId,
                    'event_id' => $ticketType->eventId,
                    'session_id' => $ticketType->sessionId,
                    'subtotal_amount' => $lineTotal,
                    'total_amount' => $lineTotal,
                ]);
                $order->forceFill([
                    'hold_expires_at' => $this->clock->now()->addMinutes(
                        (int) $this->settings->get('order.hold_expiry_minutes'),
                    ),
                ])->save();

                $order->items()->create([
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $quantity,
                    'unit_amount_snapshot' => $ticketType->currentPrice,
                    'ticket_type_name_snapshot' => $ticketType->name,
                    'line_total_amount' => $lineTotal,
                ]);

                return $order;
            });
        } catch (\Throwable $e) {
            $this->capacity->release($key, $quantity);
            throw $e;
        }

        event(new OrderHeld(
            orderPublicId: $order->public_id,
            userId: $userId,
            expiresAt: $order->hold_expires_at->toIso8601String(),
        ));

        return $order->refresh();
    }

    public function counterKey(int $ticketTypeId): string
    {
        return "orders:capacity:tt:{$ticketTypeId}";
    }
}
