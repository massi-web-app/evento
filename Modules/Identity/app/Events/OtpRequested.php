<?php

declare(strict_types=1);

namespace Modules\Identity\Events;

use Modules\Identity\Enums\OtpChannel;
use Modules\Identity\Enums\OtpPurpose;
use Modules\Shared\Events\DomainEvent;
use SensitiveParameter;

final readonly class OtpRequested extends DomainEvent
{
    public function __construct(
        public string $identifier,
        public OtpChannel $channel,
        public OtpPurpose $purpose,
        #[SensitiveParameter] public string $plainCode,
        public int $ttlSeconds,
    ) {
        parent::__construct();
    }

}
