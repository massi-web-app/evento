<?php

declare(strict_types=1);

namespace Modules\Events\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class EventDrafted extends DomainEvent
{
    public function __construct(
        public string $eventPublicId,
        public int $organizerId,
    )
    {
        parent::__construct();
    }

}
