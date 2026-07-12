<?php
declare(strict_types=1);

namespace Modules\Orders\Enums;

enum PaymentStatus: int
{
    case Initiated = 1;
    case Redirected = 2;
    case Verified = 3;
    case Failed = 4;
    case Reversed = 5;

    public function isFinal(): bool
    {
        return in_array($this, [self::Verified, self::Failed, self::Reversed], strict: true);
    }
}
