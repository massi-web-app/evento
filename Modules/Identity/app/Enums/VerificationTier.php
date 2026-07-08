<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum VerificationTier: int
{
    case None = 1;
    case Bronze = 2;
    case Silver = 3;
    case Gold = 4;

    public function label(): string
    {
        return match ($this) {
            self::None => 'تأییدنشده',
            self::Bronze => 'برنزی',
            self::Silver => 'نقره‌ای',
            self::Gold => 'طلایی',
        };
    }
}
