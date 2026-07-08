<?php

declare(strict_types=1);

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Role extends Model
{
    protected $fillable = ['name', 'display_name', 'description'];

    protected $casts = ['is_system' => 'boolean'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
