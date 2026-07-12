<?php

declare(strict_types=1);

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Events\Database\Factories\EventFactory;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Enums\EventStatus;
use Modules\Shared\Concerns\HasPublicId;

final class Event extends Model
{

    use HasFactory;
    use HasPublicId;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'venue_id',
        'city_id',
        'title',
        'slug',
        'summary',
        'description',
        'cover_url',
        'format',
        'starts_at',
        'ends_at',
        'capacity_total',
    ];

    protected $casts = [
        'format' => EventFormat::class,
        'status' => EventStatus::class,
        'starts_at' => 'immutable_datetime',
        'ends_at' => 'immutable_datetime',
        'published_at' => 'immutable_datetime',
        'is_featured' => 'boolean',
        'capacity_total' => 'integer',
        'tickets_sold_cache' => 'integer',
        'rating_cache' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 1,               // EventStatus::Draft — درس Organizer
        'is_featured' => false,
        'tickets_sold_cache' => 0,
    ];

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    public function isSoldOut(): bool
    {
        return $this->capacity_total !== null
            && $this->tickets_sold_cache >= $this->capacity_total;
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class)
            ->orderBy('starts_at');
    }


}
