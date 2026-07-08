<?php

declare(strict_types=1);

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Setting extends Model
{
    protected $fillable = [
        'definition_id',
        'scope_type',
        'scope_id',
        'value',
        'updated_by',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(SettingDefinition::class, 'definition_id');
    }
}
