<?php
declare(strict_types=1);

namespace Modules\Events\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Shared\Concerns\HasPublicId;
use Modules\Shared\ValueObjects\Money;

final class TicketType extends Model
{
    use HasPublicId;
    protected $fillable = [
        'name',
        'description',
        'capacity',
        'min_per_order',
        'max_per_order',
        'sort_order',
    ];


    protected $casts = [
        'capacity' => 'integer',
        'sold_cache' => 'integer',
        'min_per_order' => 'integer',
        'max_per_order' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'sold_cache' => 0,
        'min_per_order' => 1,
        'max_per_order' => 10,
        'is_active' => true,
        'sort_order' => 0,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'session_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(TicketTypePrice::class);
    }

    public function currentPrice(?CarbonImmutable $at = null): ?Money
    {
        $at ??= CarbonImmutable::now();

        /** @var TicketTypePrice|null $price */
        $price = $this->prices
            ->filter(fn (TicketTypePrice $p): bool => $p->isActiveAt($at))
            ->sortByDesc('starts_at')
            ->first();

        return $price?->amount;
    }

    public function remainingCapacity(): int
    {
        return max(0, $this->capacity - $this->sold_cache);
    }
}
