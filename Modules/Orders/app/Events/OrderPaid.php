<?php

declare(strict_types=1);

namespace Modules\Orders\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class OrderPaid extends DomainEvent
{

    public function __construct(
        public  string $orderPublicId,
    ) {
        parent::__construct();
    }
}
