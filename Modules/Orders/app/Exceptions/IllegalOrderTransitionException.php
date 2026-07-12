<?php
declare(strict_types=1);

namespace Modules\Orders\Exceptions;


use Modules\Orders\Enums\orderStatus;
use RuntimeException;

final class IllegalOrderTransitionException extends RuntimeException
{

    public static function between(OrderStatus $from, OrderStatus $to): self
    {
        return new self("Cannot transition order from [{$from->name}] to [{$to->name}].");
    }
}
