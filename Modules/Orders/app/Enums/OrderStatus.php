<?php

declare(strict_types=1);

namespace Modules\Orders\Enums;

enum OrderStatus: int
{
    case Pending = 1;
    case AwaitingPayment = 2;
    case Paid = 3;
    case Canceled = 4;
    case Expired = 5;
    case Refunded = 6;
    case PartiallyRefunded = 7;

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::AwaitingPayment, self::Canceled, self::Expired],
            self::AwaitingPayment => [self::Paid, self::Canceled, self::Expired],
            self::Paid => [self::Refunded, self::PartiallyRefunded],
            self::PartiallyRefunded => [self::Refunded],
            self::Canceled, self::Expired, self::Refunded => [],
        };
    }


    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), strict: true);
    }

    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    public function holdsCapacity(): bool
    {
        return in_array($this, [self::Pending, self::AwaitingPayment], strict: true);
    }


}
