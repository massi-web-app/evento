<?php

declare(strict_types=1);

namespace Modules\Settings\Exceptions;

use RuntimeException;

final class SettingNotDefinedException extends RuntimeException
{
    public static function forKey(string $key): self
    {
        return new self("Setting [{$key}] is not defined in the registry. Did you forget to seed it?");
    }
}
