<?php

declare(strict_types=1);
namespace Modules\Events\Contracts;

use Modules\Events\DTOs\SellableTicketType;
use Modules\Events\Exceptions\TicketTypeNotSellableException;

interface SellableTicketTypes
{
    /** @throws TicketTypeNotSellableException */
    public function byPublicId(string $publicId): SellableTicketType;


}
