<?php

declare(strict_types=1);

namespace Modules\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public int $amount,
        public string $currency,
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

    }

    public static function irr(int $amount): self
    {
        return new self($amount, 'IRR');
    }

    public static function of(int $amount, string $currency = 'IRR'): self
    {
        return new self($amount, strtoupper($currency));
    }

    public static function zero(string $currency = 'IRR'): self
    {
        return new self(0, $currency);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function percentage(int $basisPoints): self
    {
        return new self(
            intdiv($this->amount * $basisPoints, 10_000),
            $this->currency,
        );
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}"
            );
        }
    }
}
