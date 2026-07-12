<?php

declare(strict_types=1);

namespace Modules\Orders\Exceptions;

use RuntimeException;

final class OrderNotPaidException extends RuntimeException
{
    public static function forPublicId(string $publicId): self
    {
        return new self("Order [{$publicId}] is not in Paid status.");
    }

}
