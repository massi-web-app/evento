<?php

declare(strict_types=1);

namespace Modules\Identity\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class OrganizerRegistered extends DomainEvent
{
    public function __construct(
        public string $organizerPublicId,
        public string $ownerPublicId,
        public string $brandName,
    ) {
        parent::__construct();
    }
}
