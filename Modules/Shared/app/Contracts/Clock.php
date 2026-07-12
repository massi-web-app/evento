<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

use Carbon\CarbonImmutable;

interface Clock
{
    public function now(): CarbonImmutable;
}
