<?php

declare(strict_types=1);

namespace Modules\Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Modules\Shared\ValueObjects\Money;

/**
 * @implements CastsAttributes<Money, Money>
 */
final readonly class MoneyCast implements CastsAttributes
{


    public function __construct(
        private  string $mode = 'with_currency',
    ) {}

    public static function castUsing(array $arguments): never
    {
        throw new LogicException('Use MoneyCast::class directly with optional :without_currency argument.');
    }


    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return Money::of((int) $value, (string) ($attributes['currency'] ?? 'IRR'));
    }
    /**
     * @param array<string, mixed> $attributes
     * @return array<string, int|string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if (! $value instanceof Money) {
            throw new \InvalidArgumentException("The {$key} attribute must be a Money instance.");
        }

        $columns = [$key => $value->amount];

        if ($this->mode !== 'without_currency') {
            $columns['currency'] = $value->currency;
        }

        return $columns;
    }
}
