<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum OtpChannel: int
{
    case Sms = 1;
    case Email = 2;
}
