<?php

declare(strict_types=1);

namespace Modules\Orders\Contracts;

interface CapacityCounter
{
    public function tryAcquire(string $key, int $quantity): bool;

    public function release(string $key, int $quantity): void;

    public function initializeIfMissing(string $key, int $capacity): void;

}
