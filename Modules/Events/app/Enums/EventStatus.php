<?php

declare(strict_types=1);

namespace Modules\Events\Enums;


enum EventStatus: int
{
    case Draft = 1;
    case PendingReview = 2;
    case Approved = 3;
    case Published = 4;
    case Paused = 5;
    case Ended = 6;
    case Canceled = 7;
    case Rejected = 8;

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::PendingReview],
            self::PendingReview => [self::Approved, self::Rejected],
            self::Approved => [self::Published],
            self::Published => [self::Paused, self::Ended, self::Canceled],
            self::Paused => [self::Published, self::Canceled],
            self::Ended, self::Canceled, self::Rejected => [],   // بن‌بست‌ها
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), strict: true);
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this, [self::Published, self::Paused, self::Ended], strict: true);
    }

    public function isSellable(): bool
    {
        return $this === self::Published;
    }

}
