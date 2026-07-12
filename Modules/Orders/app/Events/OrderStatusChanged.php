<?php

declare(strict_types=1);

namespace Modules\Orders\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class OrderStatusChanged extends DomainEvent
{
    public function __construct(
        public  string $orderPublicId,
        public  int $from,
        public  int $to,
        public  ?int $actorUserId,
        public  ?string $reason,
    ) {
        parent::__construct();
    }


}
