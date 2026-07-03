<?php

declare(strict_types=1);

namespace Modules\Identity\Exceptions;

use RuntimeException;

final class InvalidOtpException extends RuntimeException
{

    public static function wrongCode(): self
    {
        return new self('The provided OTP code is invalid.');
    }

    public static function expiredOrMissing(): self
    {
        return new self('No valid OTP found for this identifier.');
    }

    public static function attemptsExhausted(): self
    {
        return new self('Maximum verification attempts exceeded.');
    }

}
