<?php

declare(strict_types=1);

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Ledger\Enums\EntryDirection;
use Modules\Shared\Casts\MoneyCast;

final class LedgerEntry extends Model
{
    public const string|null UPDATED_AT = null;

    protected $fillable = ['account_id', 'direction', 'amount'];

    protected $casts = [
        'direction' => EntryDirection::class,
        'amount' => MoneyCast::class . ':without_currency',
    ];
}
