<?php

declare(strict_types=1);

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Events\Enums\SessionStatus;
use Modules\Shared\Concerns\HasPublicId;

final class EventSession extends Model
{
    use HasPublicId;


    protected $fillable = ['title', 'starts_at', 'ends_at', 'capacity'];

    protected $casts = [
        'status' => SessionStatus::class,
        'starts_at' => 'immutable_datetime',
        'ends_at' => 'immutable_datetime',
        'capacity' => 'integer',
    ];

    protected $attributes = [
        'status' => 1,   // SessionStatus::Scheduled
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketType::class, 'session_id')
            ->orderBy('sort_order');
    }
}
