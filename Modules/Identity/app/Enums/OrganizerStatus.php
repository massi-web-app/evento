<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum OrganizerStatus: int
{
    case Pending = 1;
    case Active = 2;
    case Suspended = 3;
    case Rejected = 4;

    /** State Machine سبک: هر وضعیت فقط به مقصدهای مجاز می‌رود */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, match ($this) {
            self::Pending => [self::Active, self::Rejected],
            self::Active => [self::Suspended],
            self::Suspended => [self::Active],
            self::Rejected => [],                    // بن‌بست — درخواست جدید لازم است
        }, strict: true);
    }

    public function canCreateEvents(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار بررسی',
            self::Active => 'فعال',
            self::Suspended => 'معلق',
            self::Rejected => 'رد شده',
        };
    }
}
