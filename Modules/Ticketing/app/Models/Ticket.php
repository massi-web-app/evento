<?php

declare(strict_types=1);

namespace Modules\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Concerns\HasPublicId;
use Modules\Ticketing\Enums\TicketStatus;

final class Ticket extends Model
{
    use HasPublicId;

    protected $fillable = [
        'order_item_id',
        'event_id',
        'session_id',
        'ticket_type_id',
        'holder_user_id',
        'checkin_code',
        'issued_at',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'issued_at' => 'immutable_datetime',
        'checked_in_at' => 'immutable_datetime',
    ];

    protected $attributes = [
        'status' => 1,
    ];

}
