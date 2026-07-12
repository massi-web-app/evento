<?php

declare(strict_types=1);

namespace Modules\Events\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class EventStatusChanged extends DomainEvent
{

    public function __construct(
        public  string $eventPublicId,
        public  int $from,
        public  int $to,
        public  ?int $actorUserId,
        public  ?string $reason,
    ) {
        parent::__construct();
    }

}
