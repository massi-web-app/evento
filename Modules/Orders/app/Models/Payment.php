<?php
declare(strict_types=1);

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Orders\Enums\PaymentStatus;
use Modules\Shared\Casts\MoneyCast;
use Modules\Shared\Concerns\HasPublicId;

final class Payment extends Model
{
    use HasPublicId;

    protected $fillable = ['order_id', 'gateway', 'amount'];
    protected $casts = [
        'status' => PaymentStatus::class,
        'amount' => MoneyCast::class,
        'gateway_meta' => 'array',
        'verified_at' => 'immutable_datetime',
    ];

    protected $attributes = [
        'status' => 1,
        'currency' => 'IRR',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }


}
