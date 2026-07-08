<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum MemberRole: int
{
    case Owner = 1;
    case Admin = 2;
    case Manager = 3;
    case CheckinStaff = 4;
    case Marketer = 5;

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'مالک',
            self::Admin => 'مدیر',
            self::Manager => 'سرپرست',
            self::CheckinStaff => 'مسئول پذیرش',
            self::Marketer => 'بازاریاب',
        };
    }
}
