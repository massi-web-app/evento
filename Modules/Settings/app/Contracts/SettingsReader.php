<?php

declare(strict_types=1);

namespace Modules\Settings\Contracts;

interface SettingsReader
{
    public function get(string $key, ?string $scopeType = null, ?int $scopeId = null): int|float|string|bool|array;
}
