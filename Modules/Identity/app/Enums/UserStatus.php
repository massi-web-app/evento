<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum UserStatus: int
{
    case Pending = 1;
    case Active = 2;
    case Suspended = 3;
    case Banned = 4;

    public function canAuthenticate(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار تأیید',
            self::Active => 'فعال',
            self::Suspended => 'معلق',
            self::Banned => 'مسدود',
        };
    }

}
