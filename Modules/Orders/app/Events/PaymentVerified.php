<?php
declare(strict_types=1);
namespace Modules\Orders\Events;

use Modules\Shared\Events\DomainEvent;

final readonly class PaymentVerified extends DomainEvent
{
    public function __construct(
        public  string $paymentPublicId,
        public  string $orderPublicId,
        public  int $amount,
        public  string $gatewayRef,
    ) {
        parent::__construct();
    }

}
