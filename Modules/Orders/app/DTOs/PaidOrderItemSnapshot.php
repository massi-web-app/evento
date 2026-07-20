<?php
declare(strict_types=1);
namespace Modules\Orders\DTOs;

use Spatie\LaravelData\Data;

final class PaidOrderItemSnapshot extends Data
{

    public function __construct(
        public readonly int $orderItemId,
        public readonly int $ticketTypeId,
        public readonly int $quantity,
    ) {}
}
