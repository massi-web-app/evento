<?php

declare(strict_types=1);

namespace Modules\Ticketing\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class TicketsIssued extends DomainEvent
{
    public function __construct(
        public  string $orderPublicId,
        public  int $count,
    ) {
        parent::__construct();
    }

}
