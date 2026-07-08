<?php

declare(strict_types=1);

namespace Modules\Identity\Exceptions;

use Modules\Identity\Enums\UserStatus;
use RuntimeException;

final class AccountNotAllowedException extends RuntimeException
{
    public static function forStatus(UserStatus $status): self
    {
        return new self("Account cannot authenticate (status: {$status->name}).");
    }
}
