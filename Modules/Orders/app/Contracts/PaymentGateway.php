<?php

declare(strict_types=1);

namespace Modules\Orders\Contracts;

use Modules\Orders\DTOs\GatewayInitResult;
use Modules\Orders\DTOs\GatewayVerifyResult;
use Modules\Shared\ValueObjects\Money;

interface PaymentGateway
{
    /** شناسهٔ یکتای درگاه — همان که در ستون gateway می‌نشیند. */
    public function name(): string;

    /** شروع پرداخت: مبلغ + شناسهٔ مرجع ما → token و آدرس redirect. */
    public function initiate(Money $amount, string $callbackUrl, string $reference): GatewayInitResult;

    /** راستی‌آزمایی server-side بعد از callback. */
    public function verify(string $gatewayToken, Money $expectedAmount): GatewayVerifyResult;

}
