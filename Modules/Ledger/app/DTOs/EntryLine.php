<?php
declare(strict_types=1);

namespace Modules\Ledger\DTOs;


use Modules\Ledger\Enums\EntryDirection;
use Modules\Shared\ValueObjects\Money;
use Spatie\LaravelData\Data;

class EntryLine extends Data
{
    private function __construct(
        public string $accountCode,
        public EntryDirection $direction,
        public Money $amount,
    ) {}

    public static function debit(string $accountCode, Money $amount): self
    {
        return new self($accountCode, EntryDirection::Debit, $amount);
    }

    public static function credit(string $accountCode, Money $amount): self
    {
        return new self($accountCode, EntryDirection::Credit, $amount);
    }

}
