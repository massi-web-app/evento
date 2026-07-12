<?php

declare(strict_types=1);

namespace Modules\Shared\Support;

use Carbon\CarbonImmutable;
use Modules\Shared\Contracts\Clock;

final class SystemClock implements Clock
{
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }
}
