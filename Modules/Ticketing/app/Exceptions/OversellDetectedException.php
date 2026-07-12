<?php
declare(strict_types=1);

namespace Modules\Ticketing\Exceptions;

use RuntimeException;

final class OversellDetectedException extends RuntimeException
{
    public static function forTicketType(int $ticketTypeId, int $requested): self
    {
        return new self(
            "Capacity judge blocked issuing {$requested} ticket(s) for ticket_type [{$ticketTypeId}] — Redis/DB drift?",
        );
    }

}
