<?php

declare(strict_types=1);

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Database\Factories\CityFactory;

final class City extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['province_id', 'name', 'slug', 'latitude', 'longitude', 'is_major'];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_major' => 'boolean',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    protected static function newFactory(): CityFactory
    {
        return CityFactory::new();

    }

}
