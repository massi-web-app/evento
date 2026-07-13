<?php

declare(strict_types=1);

namespace Modules\Ledger\Enums;
enum AccountType: int
{
    case Asset = 1;
    case Liability = 2;
    case Revenue = 3;
    case Expense = 4;

    public function increasesWith(): EntryDirection
    {
        return match ($this) {
            self::Asset, self::Expense => EntryDirection::Debit,
            self::Liability, self::Revenue => EntryDirection::Credit,
        };
    }

}
