<?php

declare(strict_types=1);

namespace Modules\Orders\DTOs;

use Spatie\LaravelData\Data;

final class PaidOrderSnapshot extends Data
{

    /** @param list<PaidOrderItemSnapshot> $items */
    public function __construct(
        public readonly int $userId,
        public readonly int $eventId,
        public readonly int $sessionId,
        public readonly array $items,
    ) {}
}
