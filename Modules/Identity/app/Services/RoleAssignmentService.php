<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use Modules\Identity\Contracts\PermissionChecker;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;

final readonly class RoleAssignmentService
{
    public function __construct(
        private PermissionChecker $permissionChecker,
    ) {}

    public function assign(User $user, Role $role, ?User $assignedBy = null): void
    {
        $user->roles()->syncWithoutDetaching([
            $role->id => ['assigned_by' => $assignedBy?->id, 'assigned_at' => now()],
        ]);

        $this->permissionChecker->forgetFor($user->id);
    }

    public function revoke(User $user, Role $role): void
    {
        $user->roles()->detach($role->id);

        $this->permissionChecker->forgetFor($user->id);
    }
}
