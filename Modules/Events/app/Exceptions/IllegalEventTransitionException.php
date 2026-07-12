<?php

declare(strict_types=1);

namespace Modules\Events\Exceptions;

use Modules\Events\Enums\EventStatus;
use RuntimeException;

final class IllegalEventTransitionException extends RuntimeException
{
    public static function between(EventStatus $from, EventStatus $to): self
    {
        return new self("Cannot transition event from [{$from->name}] to [{$to->name}].");
    }

}
