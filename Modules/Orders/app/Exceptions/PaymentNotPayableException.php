<?php
declare(strict_types=1);

namespace Modules\Orders\Exceptions;

use RuntimeException;

final class PaymentNotPayableException extends RuntimeException
{
    public static function holdExpired(string $orderPublicId): self
    {
        return new self("Order [{$orderPublicId}] hold has expired.");
    }

    public static function wrongStatus(string $orderPublicId, string $status): self
    {
        return new self("Order [{$orderPublicId}] in status [{$status}] cannot be paid.");
    }

}
