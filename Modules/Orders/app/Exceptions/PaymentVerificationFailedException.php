<?php

declare(strict_types=1);
namespace Modules\Orders\Exceptions;

use RuntimeException;

final class PaymentVerificationFailedException extends RuntimeException
{

    public static function because(string $reason): self
    {
        return new self("Payment verification failed: {$reason}.");
    }
}
