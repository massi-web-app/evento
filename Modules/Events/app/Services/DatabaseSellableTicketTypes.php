<?php

declare(strict_types=1);

namespace Modules\Events\Services;

use Modules\Events\Contracts\SellableTicketTypes;
use Modules\Events\DTOs\SellableTicketType;
use Modules\Events\Exceptions\TicketTypeNotSellableException;
use Modules\Events\Models\TicketType;
use Modules\Shared\Contracts\Clock;

final readonly class DatabaseSellableTicketTypes implements SellableTicketTypes
{
    public function __construct(
        private Clock $clock,
    ) {}

    public function byPublicId(string $publicId): SellableTicketType
    {
        /** @var TicketType|null $tt */
        $tt = TicketType::query()
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->with(['prices', 'session'])
            ->first();

        $price = $tt?->currentPrice($this->clock->now());

        if ($tt === null || $price === null || ! $tt->session->status->isBookable()) {
            throw TicketTypeNotSellableException::forPublicId($publicId);
        }

        return new SellableTicketType(
            id: $tt->id,
            publicId: $tt->public_id,
            name: $tt->name,
            eventId: $tt->session->event_id,
            sessionId: $tt->session->id,
            minPerOrder: $tt->min_per_order,
            maxPerOrder: $tt->max_per_order,
            remainingCapacity: $tt->remainingCapacity(),
            currentPrice: $price,
        );
    }
}
