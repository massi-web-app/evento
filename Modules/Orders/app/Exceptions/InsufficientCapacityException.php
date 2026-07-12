<?php

declare(strict_types=1);

namespace Modules\Orders\Exceptions;

use RuntimeException;

final class InsufficientCapacityException extends RuntimeException
{
    public static function forTicketType(string $ticketTypePublicId, int $requested): self
    {
        return new self("Not enough capacity on ticket type [{$ticketTypePublicId}] for {$requested} seats.");
    }

    public static function notOnSale(string $ticketTypePublicId): self
    {
        return new self("Ticket type [{$ticketTypePublicId}] is not currently on sale.");
    }

}
