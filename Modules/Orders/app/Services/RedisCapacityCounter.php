<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\Redis;
use Modules\Orders\Contracts\CapacityCounter;

final class RedisCapacityCounter implements CapacityCounter
{
    private const ACQUIRE_SCRIPT = <<<'LUA'
        local current = tonumber(redis.call('GET', KEYS[1]) or '-1')
        if current < 0 then return -1 end
        if current < tonumber(ARGV[1]) then return 0 end
        redis.call('DECRBY', KEYS[1], ARGV[1])
        return 1
    LUA;

    public function tryAcquire(string $key, int $quantity): bool
    {
        $result = (int) Redis::eval(self::ACQUIRE_SCRIPT, 1, $key, $quantity);

        if ($result === -1) {
            // شمارنده هنوز initialize نشده — مسئولیت صدازننده است؛ محافظه‌کارانه رد کن
            return false;
        }

        return $result === 1;
    }

    public function release(string $key, int $quantity): void
    {
        Redis::incrby($key, $quantity);
    }

    public function initializeIfMissing(string $key, int $capacity): void
    {
        Redis::set($key, $capacity, 'NX');   // فقط اگر نیست — بدون بازنویسی شمارش جاری
    }
}
