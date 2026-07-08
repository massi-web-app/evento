<?php

declare(strict_types=1);

namespace Modules\Identity\Exceptions;

use RuntimeException;

final class OtpRateLimitExceededException extends RuntimeException
{
    public static function forSeconds(int $retryAfter): self
    {
        $e = new self("Too many OTP requests. Retry after {$retryAfter} seconds.");
        $e->retryAfter = $retryAfter;

        return $e;
    }

    public int $retryAfter = 0;
}
