<?php

declare(strict_types=1);

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Catalog\Database\Factories\VenueFactory;
use Modules\Shared\Concerns\HasPublicId;

final class Venue extends Model
{

    use HasFactory;
    use HasPublicId;
    use SoftDeletes;


    protected $fillable = [
        'city_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'capacity',
        'amenities',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'capacity' => 'integer',
        'amenities' => 'array',
        'is_verified' => 'boolean',
    ];

    protected $attributes = [
        'is_verified' => false,
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    protected static function newFactory(): VenueFactory
    {
        return VenueFactory::new();
    }
}
