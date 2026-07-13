<?php

declare(strict_types=1);

namespace Modules\Events\DTOs;

use Modules\Shared\ValueObjects\Money;
use Spatie\LaravelData\Data;

final class SellableTicketType extends Data
{

    public function __construct(
        public readonly int $id,
        public readonly string $publicId,
        public readonly string $name,
        public readonly int $eventId,
        public readonly int $sessionId,
        public readonly int $minPerOrder,
        public readonly int $maxPerOrder,
        public readonly int $remainingCapacity,
        public readonly Money $currentPrice,
    ) {}
}
