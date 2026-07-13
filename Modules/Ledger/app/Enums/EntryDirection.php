<?php
declare(strict_types=1);

namespace Modules\Ledger\Enums;

enum EntryDirection: int
{
    case Debit = 1;
    case Credit = 2;
}
