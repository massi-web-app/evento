<?php

declare(strict_types=1);

namespace Modules\Identity\DTOs;

use Spatie\LaravelData\Data;

final class AuthResult extends Data
{
    public function __construct(
        public readonly string $token,
        public readonly string $userPublicId,
        public readonly bool $isNewUser,
    ) {}

}
