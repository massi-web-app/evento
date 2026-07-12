<?php

declare(strict_types=1);

namespace Modules\Events\Enums;

enum EventFormat: int
{
    case InPerson = 1;
    case Online = 2;
    case Hybrid = 3;

    public function label(): string
    {
        return match ($this) {
            self::InPerson => 'حضوری',
            self::Online => 'آنلاین',
            self::Hybrid => 'ترکیبی',
        };
    }


    public function requiresVenue(): bool
    {
        return $this !== self::Online;
    }
}
