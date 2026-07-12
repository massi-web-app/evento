<?php

declare(strict_types=1);

namespace Modules\Events\Exceptions;

use RuntimeException;

final class OrganizerNotActiveException extends RuntimeException
{
    public static function forId(int $organizerId): self
    {
        return new self("Organizer [{$organizerId}] is not active and cannot create events.");
    }
}
