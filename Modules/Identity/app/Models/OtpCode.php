<?php

declare(strict_types=1);

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Enums\OtpChannel;
use Modules\Identity\Enums\OtpPurpose;

final class OtpCode extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'identifier',
        'channel',
        'purpose',
        'code_hash',
        'max_attempts',
        'expires_at',
    ];

    protected $casts = [
        'channel' => OtpChannel::class,
        'purpose' => OtpPurpose::class,
        'expires_at' => 'immutable_datetime',
        'consumed_at' => 'immutable_datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function hasAttemptsLeft(): bool
    {
        return $this->attempts < $this->max_attempts;
    }
}
