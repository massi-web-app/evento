<?php

declare(strict_types=1);

namespace Modules\Events\Exceptions;

use RuntimeException;

final class TicketTypeNotSellableException extends RuntimeException
{
    public static function forPublicId(string $publicId): self
    {
        return new self("Ticket type [{$publicId}] is not currently sellable (inactive, no price window, or session not bookable).");
    }
}
