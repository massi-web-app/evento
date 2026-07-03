<?php

declare(strict_types=1);

namespace Modules\Identity\Enums;

enum OtpPurpose: int
{
    case Login = 1;
    case VerifyPhone = 2;
    case VerifyEmail = 3;
    case ResetPassword = 4;
    case TwoFactor = 5;

}
