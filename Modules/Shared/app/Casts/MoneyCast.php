<?php

declare(strict_types=1);

namespace Modules\Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\ValueObjects\Money;

/**
 * @implements CastsAttributes<Money, Money>
 */
final class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return Money::of((int) $value, $attributes['currency'] ?? 'IRR');

    }

    /**
     * @param  array<string, mixed>  $attributes
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

        return [
            $key => $value->amount,
            'currency' => $value->currency,
        ];
    }
}
