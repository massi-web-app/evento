<?php

declare(strict_types=1);

namespace Modules\Events\Enums;

enum SessionStatus: int
{
    case Scheduled = 1;
    case Canceled = 2;
    case Completed = 3;


    public function isBookable(): bool
    {
        return $this === self::Scheduled;
    }

}
