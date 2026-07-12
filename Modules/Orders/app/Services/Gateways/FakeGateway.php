<?php

declare(strict_types=1);

namespace Modules\Orders\Services\Gateways;

use Illuminate\Support\Str;
use Modules\Orders\Contracts\PaymentGateway;
use Modules\Orders\DTOs\GatewayInitResult;
use Modules\Orders\DTOs\GatewayVerifyResult;
use Modules\Shared\ValueObjects\Money;

final class FakeGateway implements PaymentGateway
{

    public function name(): string
    {
        return 'fake';
    }

    public function initiate(Money $amount, string $callbackUrl, string $reference): GatewayInitResult
    {
        $token = 'FAKE-' . Str::upper(Str::random(20));

        return new GatewayInitResult(
            gatewayToken: $token,
            redirectUrl: $callbackUrl . '?token=' . $token . '&simulate=1',
        );
    }

    public function verify(string $gatewayToken, Money $expectedAmount): GatewayVerifyResult
    {
        if (str_starts_with($gatewayToken, 'FAIL')) {
            return GatewayVerifyResult::fail('simulated_failure', ['token' => $gatewayToken]);
        }

        return GatewayVerifyResult::ok(
            referenceId: 'REF-' . Str::upper(Str::random(12)),
            raw: ['token' => $gatewayToken, 'amount' => $expectedAmount->amount],
        );
    }
}
