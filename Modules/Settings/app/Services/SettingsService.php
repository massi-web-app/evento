<?php

declare(strict_types=1);

namespace Modules\Settings\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Settings\Contracts\SettingsReader;
use Modules\Settings\Exceptions\SettingNotDefinedException;
use Modules\Settings\Models\Setting;
use Modules\Settings\Models\SettingDefinition;

final class SettingsService implements SettingsReader
{
    private const int TTL_SECONDS = 3600;

    private array $resolved = [];

    public function __construct(
        private readonly Cache $cache,
    ) {}

    public function get(string $key, ?string $scopeType = null, ?int $scopeId = null): int|float|string|bool|array
    {
        $memoKey = "{$key}|{$scopeType}|{$scopeId}";

        return $this->resolved[$memoKey] ??= $this->resolveThroughCache($key, $scopeType, $scopeId);
    }

    public function set(string $key, mixed $value, string $scopeType = 'global', ?int $scopeId = null, ?int $updatedBy = null): void
    {
        $definition = $this->definitionOrFail($key);

        if ($scopeType !== 'global' && ! $definition->is_overridable) {
            throw new \InvalidArgumentException("Setting [{$key}] is not overridable per scope.");
        }

        Setting::query()->updateOrCreate(
            ['definition_id' => $definition->id, 'scope_type' => $scopeType, 'scope_id' => $scopeId],
            ['value' => $definition->value_type->castToStorage($value), 'updated_by' => $updatedBy],
        );

        $this->cache->increment($this->versionKey());
        $this->resolved = [];
    }

    private function resolveThroughCache(string $key, ?string $scopeType, ?int $scopeId): int|float|string|bool|array
    {
        $version = (int) $this->cache->get($this->versionKey(), 1);

        return $this->cache->remember(
            "settings:v{$version}:{$key}:{$scopeType}:{$scopeId}",
            self::TTL_SECONDS,
            fn () => $this->resolveFresh($key, $scopeType, $scopeId),
        );
    }

    private function resolveFresh(string $key, ?string $scopeType, ?int $scopeId): int|float|string|bool|array
    {
        $definition = $this->definitionOrFail($key);

        // پلهٔ ۱: override در scope مشخص
        if ($scopeType !== null && $definition->is_overridable) {
            $scoped = $this->valueFor($definition->id, $scopeType, $scopeId);
            if ($scoped !== null) {
                return $definition->value_type->castFromStorage($scoped);
            }
        }

        // پلهٔ ۲: مقدار global
        $global = $this->valueFor($definition->id, 'global', null);
        if ($global !== null) {
            return $definition->value_type->castFromStorage($global);
        }

        // پلهٔ ۳: default رجیستری
        return $definition->value_type->castFromStorage($definition->default_value);
    }

    private function valueFor(int $definitionId, string $scopeType, ?int $scopeId): ?string
    {
        return Setting::query()
            ->where('definition_id', $definitionId)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->value('value');
    }

    private function definitionOrFail(string $key): SettingDefinition
    {
        return SettingDefinition::query()->where('key', $key)->first()
            ?? throw SettingNotDefinedException::forKey($key);
    }

    private function versionKey(): string
    {
        return 'settings:version';
    }
}
