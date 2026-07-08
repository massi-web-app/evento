<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;

uses(RefreshDatabase::class);

it('assigns and detects a role', function (): void {
    $this->seed(RbacSeeder::class);
    $user = User::factory()->create();

    $user->roles()->attach(Role::query()->where('name', 'organizer')->firstOrFail());

    expect($user->hasRole('organizer'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse();
});

it('resolves permissions through roles', function (): void {
    $role = Role::query()->create(['name' => 'moderator', 'display_name' => 'ناظر']);
    $permission = Permission::query()->create(['name' => 'reviews.hide', 'display_name' => 'مخفی‌سازی نظر']);
    $role->permissions()->attach($permission);

    $user = User::factory()->create();
    $user->roles()->attach($role);

    expect($user->hasPermission('reviews.hide'))->toBeTrue()
        ->and($user->hasPermission('reviews.delete'))->toBeFalse();
});

it('seeds system roles idempotently', function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(RbacSeeder::class);   // بار دوم — نباید duplicate شود

    expect(Role::query()->where('name', 'organizer')->count())->toBe(1);
});
