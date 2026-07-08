<?php

declare(strict_types=1);

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Settings\Enums\SettingType;

final class SettingDefinition extends Model
{
    protected $fillable = [
        'key',
        'value_type',
        'default_value',
        'group_name',
        'description',
        'is_overridable',
        'is_public',
    ];

    protected $casts = [
        'value_type' => SettingType::class,
        'is_overridable' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'definition_id');
    }
}
