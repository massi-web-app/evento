<?php

declare(strict_types=1);

namespace Modules\Shared\Events;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

abstract readonly class DomainEvent
{
    public string $eventId;

    public CarbonImmutable $occurredAt;

    public function __construct()
    {
        $this->eventId = (string) Str::ulid();
        $this->occurredAt = CarbonImmutable::now();
    }
}
