<?php
declare(strict_types=1);

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Shared\Concerns\HasPublicId;

final class LedgerTransaction extends Model
{
    use HasPublicId;

    public const string|null UPDATED_AT = null;

    protected $fillable = ['source_type', 'source_id', 'description', 'occurred_at'];

    protected $casts = ['occurred_at' => 'immutable_datetime'];

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'transaction_id');
    }
}
