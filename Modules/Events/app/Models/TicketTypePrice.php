<?php
declare(strict_types=1);

namespace Modules\Events\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Shared\Casts\MoneyCast;

final class TicketTypePrice extends Model
{
    protected $fillable = ['label', 'amount', 'currency', 'starts_at', 'ends_at'];
    protected $casts = [
        'amount' => MoneyCast::class,
        'starts_at' => 'immutable_datetime',
        'ends_at' => 'immutable_datetime',
    ];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function isActiveAt(CarbonImmutable $moment): bool
    {
        return $this->starts_at->lessThanOrEqualTo($moment)
            && ($this->ends_at === null || $this->ends_at->greaterThan($moment));
    }



}
