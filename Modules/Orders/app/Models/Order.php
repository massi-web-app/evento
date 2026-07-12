<?php

declare(strict_types=1);

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Orders\Enums\orderStatus;
use Modules\Shared\Casts\MoneyCast;
use Modules\Shared\Concerns\HasPublicId;


final class Order extends Model
{

    use HasPublicId;


    protected $fillable = [
        'user_id',
        'event_id',
        'session_id',
        'subtotal_amount',
        'service_fee_amount',
        'discount_amount',
        'total_amount',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'subtotal_amount' => MoneyCast::class,
        'service_fee_amount' => MoneyCast::class,
        'discount_amount' => MoneyCast::class,
        'total_amount' => MoneyCast::class,
        'hold_expires_at' => 'immutable_datetime',
        'paid_at' => 'immutable_datetime',
    ];

    protected $attributes = [
        'status' => 1,   // OrderStatus::Pending
        'service_fee_amount' => 0,
        'discount_amount' => 0,
        'currency' => 'IRR',
    ];


    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isHoldExpired(): bool
    {
        return $this->hold_expires_at !== null
            && $this->hold_expires_at->isPast();
    }

}
