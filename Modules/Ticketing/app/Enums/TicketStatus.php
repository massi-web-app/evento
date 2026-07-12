<?php
declare(strict_types=1);

namespace Modules\Ticketing\Enums;

enum TicketStatus: int
{

    case Issued = 1;
    case CheckedIn = 2;
    case Canceled = 3;
    case Refunded = 4;
    case Transferred = 5;

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Issued => [self::CheckedIn, self::Canceled, self::Refunded, self::Transferred],
            self::CheckedIn, self::Canceled, self::Refunded, self::Transferred => [],
        };
    }

    public function isUsable(): bool
    {
        return $this === self::Issued;
    }

}
