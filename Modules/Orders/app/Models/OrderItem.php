<?php

declare(strict_types=1);

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Shared\Casts\MoneyCast;

final class OrderItem extends Model
{
    protected $fillable = [
        'ticket_type_id',
        'quantity',
        'unit_amount_snapshot',
        'ticket_type_name_snapshot',
        'line_total_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_amount_snapshot' => MoneyCast::class . ':without_currency',
        'line_total_amount' => MoneyCast::class . ':without_currency',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

}
