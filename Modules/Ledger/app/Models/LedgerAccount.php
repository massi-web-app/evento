<?php

declare(strict_types=1);

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Ledger\Enums\AccountType;

final class LedgerAccount extends Model
{
    protected $fillable = ['code', 'type', 'owner_type', 'owner_id', 'currency'];


    protected $casts = [
        'type' => AccountType::class,
        'is_active' => 'boolean',
    ];

    protected $attributes = ['is_active' => true, 'currency' => 'IRR'];
}
