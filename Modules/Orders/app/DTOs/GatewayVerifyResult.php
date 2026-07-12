<?php

declare(strict_types=1);

namespace Modules\Orders\DTOs;

use Spatie\LaravelData\Data;

final  class GatewayVerifyResult extends Data
{

    public function __construct(
        public readonly bool $success,
        public readonly ?string $referenceId,      // ref_id درگاه در موفقیت
        public readonly ?string $failureReason,
        /** @var array<string, mixed> پاسخ خام برای gateway_meta */
        public readonly array $raw = [],
    ) {}

    public static function ok(string $referenceId, array $raw = []): self
    {
        return new self(true, $referenceId, null, $raw);
    }

    public static function fail(string $reason, array $raw = []): self
    {
        return new self(false, null, $reason, $raw);
    }
}
