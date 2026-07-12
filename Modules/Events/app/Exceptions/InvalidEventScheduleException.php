<?php

declare(strict_types=1);

namespace Modules\Events\Exceptions;

use RuntimeException;

final class InvalidEventScheduleException extends RuntimeException
{
    public static function endsBeforeStarts(): self
    {
        return new self('Event end time must be after its start time.');
    }
}
