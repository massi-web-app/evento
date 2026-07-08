<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum MemberStatus: int
{
    case Invited = 1;
    case Active = 2;
    case Suspended = 3;
    case Removed = 4;
}
