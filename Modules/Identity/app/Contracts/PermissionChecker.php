<?php

declare(strict_types=1);

namespace Modules\Identity\Contracts;

interface PermissionChecker
{

    public function userHasPermission(int $userId, string $permission): bool;

    public function userHasRole(int $userId, string $role): bool;

    /** پاک‌سازی کش دسترسی‌های یک کاربر — بعد از هر تغییر نقش */
    public function forgetFor(int $userId): void;
}
