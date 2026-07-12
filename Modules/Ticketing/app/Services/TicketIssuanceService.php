<?php

declare(strict_types=1);

namespace Modules\Ticketing\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Orders\Contracts\PaidOrderReader;
use Modules\Shared\Contracts\Clock;
use Modules\Ticketing\Events\TicketsIssued;
use Modules\Ticketing\Exceptions\OversellDetectedException;
use Modules\Ticketing\Models\Ticket;

final readonly class TicketIssuanceService
{
    public function __construct(
        private PaidOrderReader $orders,
        private Clock $clock,
    ) {}

    public function issueForOrder(string $orderPublicId): int
    {
        $snapshot = $this->orders->byPublicId($orderPublicId);

        $issued = DB::transaction(function () use ($snapshot): int {
            $itemIds = array_map(fn ($i) => $i->orderItemId, $snapshot->items);

            // Idempotency: صف at-least-once است؛ تحویل دوم نباید بلیت دوم بسازد
            if (Ticket::query()->whereIn('order_item_id', $itemIds)->exists()) {
                return 0;
            }

            $count = 0;

            foreach ($snapshot->items as $item) {
                // ⚖️ قاضی نهایی: UPDATE شرطی اتمیک — بدون قفل صریح، بدون پنجرهٔ race
                $claimed = DB::table('ticket_types')
                    ->where('id', $item->ticketTypeId)
                    ->whereRaw('sold_cache + ? <= capacity', [$item->quantity])
                    ->increment('sold_cache', $item->quantity);

                if ($claimed === 0) {
                    throw OversellDetectedException::forTicketType($item->ticketTypeId, $item->quantity);
                }

                foreach (range(1, $item->quantity) as $ignored) {
                    Ticket::query()->create([
                        'order_item_id' => $item->orderItemId,
                        'event_id' => $snapshot->eventId,
                        'session_id' => $snapshot->sessionId,
                        'ticket_type_id' => $item->ticketTypeId,
                        'holder_user_id' => $snapshot->userId,
                        'checkin_code' => Str::upper(Str::random(32)),
                        'issued_at' => $this->clock->now(),
                    ]);
                    $count++;
                }
            }

            return $count;
        });

        if ($issued > 0) {
            event(new TicketsIssued(orderPublicId: $orderPublicId, count: $issued));
        }

        return $issued;
    }


}
