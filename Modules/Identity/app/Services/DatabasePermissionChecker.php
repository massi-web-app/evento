<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use Modules\Identity\Contracts\PermissionChecker;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;

final class DatabasePermissionChecker implements PermissionChecker
{
    public function userHasPermission(int $userId, string $permission): bool
    {
        return Permission::query()
            ->where('name', $permission)
            ->whereHas('roles.users', fn ($q) => $q->whereKey($userId))
            ->exists();
    }

    public function userHasRole(int $userId, string $role): bool
    {
        return Role::query()
            ->where('name', $role)
            ->whereHas('users', fn ($q) => $q->whereKey($userId))
            ->exists();
    }

    public function forgetFor(int $userId): void
    {
        // پیاده‌سازی خام حافظه‌ای ندارد که فراموش کند
    }
}
