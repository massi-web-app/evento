<?php
declare (strict_types=1);

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use Modules\Events\Models\TicketType;
use Modules\Orders\Contracts\CapacityCounter;
use Modules\Orders\Events\OrderHeld;
use Modules\Orders\Exceptions\InsufficientCapacityException;
use Modules\Orders\Exceptions\InvalidQuantityException;
use Modules\Orders\Models\Order;
use Modules\Settings\Contracts\SettingsReader;
use Modules\Shared\Contracts\Clock;
use Modules\Shared\ValueObjects\Money;
use Throwable;

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
     * @throws Throwable
     */
    public function hold(int $userId, TicketType $ticketType, int $quantity): Order
    {
        if ($quantity < $ticketType->min_per_order || $quantity > $ticketType->max_per_order) {
            throw InvalidQuantityException::outOfBounds(
                $quantity, $ticketType->min_per_order, $ticketType->max_per_order,
            );
        }

        $unitPrice = $ticketType->currentPrice($this->clock->now());
        if ($unitPrice === null) {
            throw InsufficientCapacityException::notOnSale($ticketType->public_id);
        }

        $key = $this->counterKey($ticketType->id);
        $this->capacity->initializeIfMissing($key, $ticketType->remainingCapacity());

        if (! $this->capacity->tryAcquire($key, $quantity)) {
            throw InsufficientCapacityException::forTicketType($ticketType->public_id, $quantity);
        }

        try {
            $order = DB::transaction(function () use ($userId, $ticketType, $quantity, $unitPrice): Order {
                $session = $ticketType->session;
                $lineTotal = Money::of($unitPrice->amount * $quantity, $unitPrice->currency);

                $order = new Order([
                    'user_id' => $userId,
                    'event_id' => $session->event_id,
                    'session_id' => $session->id,
                    'subtotal_amount' => $lineTotal,
                    'total_amount' => $lineTotal,   // کارمزد/تخفیف در پردهٔ pricing کامل می‌شود
                ]);
                $order->forceFill([
                    'hold_expires_at' => $this->clock->now()->addMinutes(
                        (int) $this->settings->get('order.hold_expiry_minutes'),
                    ),
                ])->save();

                $order->items()->create([
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $quantity,
                    'unit_amount_snapshot' => $unitPrice,
                    'ticket_type_name_snapshot' => $ticketType->name,
                    'line_total_amount' => $lineTotal,
                ]);

                return $order;
            });
        } catch (Throwable $e) {
            // جبران: DB شکست → ظرفیت Redis را پس بده، وگرنه صندلی «گم» می‌شود
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
