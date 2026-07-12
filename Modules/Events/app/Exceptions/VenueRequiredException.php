<?php

declare(strict_types=1);

namespace Modules\Events\Exceptions;

use Modules\Events\Enums\EventFormat;
use RuntimeException;

final class VenueRequiredException extends RuntimeException
{
    public static function forFormat(EventFormat $format): self
    {
        return new self("Event format [{$format->name}] requires a venue.");
    }
}
