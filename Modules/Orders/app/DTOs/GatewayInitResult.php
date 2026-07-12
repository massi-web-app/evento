<?php

declare(strict_types=1);

namespace Modules\Orders\DTOs;

use Spatie\LaravelData\Data;

final class GatewayInitResult extends Data
{
    public function __construct(
        public readonly string $gatewayToken,
        public readonly string $redirectUrl,
    ) {}

}
