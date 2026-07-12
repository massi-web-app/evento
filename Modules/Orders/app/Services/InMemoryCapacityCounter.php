<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Modules\Orders\Contracts\CapacityCounter;

final class InMemoryCapacityCounter implements CapacityCounter
{
    /** @var array<string, int> */
    private array $counters = [];

    public function tryAcquire(string $key, int $quantity): bool
    {
        if (! isset($this->counters[$key]) || $this->counters[$key] < $quantity) {
            return false;
        }

        $this->counters[$key] -= $quantity;

        return true;
    }

    public function release(string $key, int $quantity): void
    {
        $this->counters[$key] = ($this->counters[$key] ?? 0) + $quantity;
    }

    public function initializeIfMissing(string $key, int $capacity): void
    {
        $this->counters[$key] ??= $capacity;
    }
}
