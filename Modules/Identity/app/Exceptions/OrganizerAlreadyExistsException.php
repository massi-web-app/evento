<?php

declare(strict_types=1);

namespace Modules\Identity\Exceptions;

use RuntimeException;

final class OrganizerAlreadyExistsException extends RuntimeException
{
    public static function forUser(string $userPublicId): self
    {
        return new self("User {$userPublicId} already owns an organizer.");
    }
}
