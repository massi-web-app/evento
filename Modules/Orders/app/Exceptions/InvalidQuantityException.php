<?php

declare(strict_types=1);
namespace Modules\Orders\Exceptions;

use RuntimeException;

final class InvalidQuantityException extends RuntimeException
{

    public static function outOfBounds(int $requested, int $min, int $max): self
    {
        return new self("Quantity [{$requested}] must be between {$min} and {$max}.");
    }
}
