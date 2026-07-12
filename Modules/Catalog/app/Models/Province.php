<?php
declare(strict_types=1);

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Database\Factories\ProvinceFactory;

final class Province extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['name', 'slug'];

    /**
     * @return HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public static function newFactory(): ProvinceFactory
    {
        return ProvinceFactory::new();
    }
}
