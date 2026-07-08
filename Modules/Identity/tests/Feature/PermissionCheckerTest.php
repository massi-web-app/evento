<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Contracts\PermissionChecker;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Identity\Services\RoleAssignmentService;

uses(RefreshDatabase::class);

function grantModeratorWith(string $permission): array
{
    $role = Role::query()->create(['name' => 'moderator', 'display_name' => 'ناظر']);
    $perm = Permission::query()->create(['name' => $permission, 'display_name' => $permission]);
    $role->permissions()->attach($perm);
    $user = User::factory()->create();

    return [$user, $role];
}

it('resolves permission through the contract', function (): void {
    [$user, $role] = grantModeratorWith('reviews.hide');
    app(RoleAssignmentService::class)->assign($user, $role);

    $checker = app(PermissionChecker::class);

    expect($checker->userHasPermission($user->id, 'reviews.hide'))->toBeTrue()
        ->and($checker->userHasPermission($user->id, 'reviews.nuke'))->toBeFalse();
});

it('serves the second check from cache without querying', function (): void {
    [$user, $role] = grantModeratorWith('reviews.hide');
    app(RoleAssignmentService::class)->assign($user, $role);
    $checker = app(PermissionChecker::class);

    $checker->userHasPermission($user->id, 'reviews.hide');   // پر شدن کش

    DB::enableQueryLog();
    $checker->userHasPermission($user->id, 'reviews.hide');   // باید از کش بیاید

    expect(DB::getQueryLog())->toBeEmpty();
});

it('invalidates cache when a role is revoked', function (): void {
    [$user, $role] = grantModeratorWith('reviews.hide');
    $assigner = app(RoleAssignmentService::class);
    $checker = app(PermissionChecker::class);

    $assigner->assign($user, $role);
    expect($checker->userHasPermission($user->id, 'reviews.hide'))->toBeTrue();

    $assigner->revoke($user, $role);

    expect($checker->userHasPermission($user->id, 'reviews.hide'))->toBeFalse();
});
