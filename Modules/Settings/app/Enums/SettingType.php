<?php

declare(strict_types=1);

namespace Modules\Settings\Enums;

use InvalidArgumentException;

enum SettingType: int
{
    case Integer = 1;
    case Decimal = 2;
    case String = 3;
    case Boolean = 4;
    case Json = 5;

    /** string خام دیتابیس → مقدار typed دامنه */
    public function castFromStorage(string $raw): int|float|string|bool|array
    {
        return match ($this) {
            self::Integer => (int) $raw,
            self::Decimal => (float) $raw,
            self::String => $raw,
            self::Boolean => filter_var($raw, FILTER_VALIDATE_BOOL),
            self::Json => json_decode($raw, true, 512, JSON_THROW_ON_ERROR),
        };
    }

    public function castToStorage(mixed $value): string
    {
        return match ($this) {
            self::Integer => is_int($value)
                ? (string) $value
                : throw new InvalidArgumentException('Expected int for Integer setting.'),
            self::Decimal => is_int($value) || is_float($value)
                ? (string) $value
                : throw new InvalidArgumentException('Expected numeric for Decimal setting.'),
            self::String => is_string($value)
                ? $value
                : throw new InvalidArgumentException('Expected string for String setting.'),
            self::Boolean => is_bool($value)
                ? ($value ? '1' : '0')
                : throw new InvalidArgumentException('Expected bool for Boolean setting.'),
            self::Json => json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        };
    }
}
