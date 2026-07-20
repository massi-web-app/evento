<?php

declare(strict_types=1);

namespace Modules\Ledger\Exceptions;

use RuntimeException;

final class UnbalancedTransactionException extends RuntimeException
{
    public static function withSums(int $debits, int $credits): self
    {
        return new self(
            "Ledger transaction is unbalanced: debits={$debits}, credits={$credits}. Refusing to record."
        );
    }
}
