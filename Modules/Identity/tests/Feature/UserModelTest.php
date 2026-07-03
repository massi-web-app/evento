<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Models\User;

uses(RefreshDatabase::class);


it('generates a ULID public_id on creation', function (): void {
    $user = User::factory()->create();

    expect($user->public_id)->toHaveLength(26);
});

it('casts status to UserStatus enum', function (): void {
    $user = User::factory()->pending()->create();

    expect($user->status)->toBe(UserStatus::Pending)
        ->and($user->status->canAuthenticate())->toBeFalse();
});

it('uses public_id for route model binding', function (): void {
    expect((new User())->getRouteKeyName())->toBe('public_id');
});

it('soft deletes instead of destroying the row', function (): void {
    $user = User::factory()->create();

    $user->delete();

    expect(User::withTrashed()->find($user->id))->not->toBeNull()
        ->and(User::find($user->id))->toBeNull();
});

