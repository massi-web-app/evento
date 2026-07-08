<?php

declare(strict_types=1);

namespace Modules\Identity\Services;
use Illuminate\Contracts\Cache\Repository as Cache;
use Modules\Identity\Contracts\PermissionChecker;

final readonly class CachedPermissionChecker implements PermissionChecker
{
    private const TTL_SECONDS = 3600;

    public function __construct(
        private PermissionChecker $inner,     // ← Decorator: همان قرارداد را می‌پیچد
        private Cache $cache,
    ) {}

    public function userHasPermission(int $userId, string $permission): bool
    {
        return (bool) $this->cache->remember(
            $this->key($userId, "perm:{$permission}"),
            self::TTL_SECONDS,
            fn (): bool => $this->inner->userHasPermission($userId, $permission),
        );
    }

    public function userHasRole(int $userId, string $role): bool
    {
        return (bool) $this->cache->remember(
            $this->key($userId, "role:{$role}"),
            self::TTL_SECONDS,
            fn (): bool => $this->inner->userHasRole($userId, $role),
        );
    }

    public function forgetFor(int $userId): void
    {
        $this->cache->increment($this->versionKey($userId));
    }

    private function key(int $userId, string $suffix): string
    {
        $version = (int) $this->cache->get($this->versionKey($userId), 1);

        return "rbac:u:{$userId}:v{$version}:{$suffix}";
    }

    private function versionKey(int $userId): string
    {
        return "rbac:u:{$userId}:version";
    }
}
