<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum OrganizerType: int
{
    case Individual = 1;
    case Business = 2;

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'حقیقی',
            self::Business => 'حقوقی',
        };
    }
}
