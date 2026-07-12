<?php

declare(strict_types=1);

namespace Modules\Orders\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class OrderHeld extends DomainEvent
{
    public function __construct(
        public  string $orderPublicId,
        public  int $userId,
        public  string $expiresAt,
    ) {
        parent::__construct();
    }

}
