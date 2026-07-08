<?php

declare(strict_types=1);

namespace Modules\Identity\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class UserRegistered extends DomainEvent
{
    public function __construct(
        public readonly string $userPublicId,
        public readonly string $phone,
    ) {
        parent::__construct();
    }

}
