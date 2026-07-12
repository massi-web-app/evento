<?php

declare(strict_types=1);

namespace Modules\Catalog\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'icon_url',
        'sort_order',
        'depth',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'depth' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
        'depth' => 0,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isDescendantOf(self $other): bool
    {
        return $this->path !== null
            && $other->path !== null
            && $this !== $other
            && str_starts_with($this->path, $other->path);
    }

}
