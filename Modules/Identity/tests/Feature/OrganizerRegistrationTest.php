<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Identity\Contracts\PermissionChecker;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Enums\MemberRole;
use Modules\Identity\Enums\MemberStatus;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Events\OrganizerRegistered;
use Modules\Identity\Exceptions\OrganizerAlreadyExistsException;
use Modules\Identity\Models\User;
use Modules\Identity\Services\OrganizerRegistrationService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

it('registers an organizer with owner membership, role and event', function (): void {
    Event::fake([OrganizerRegistered::class]);   // فقط همین — بقیهٔ eventها زنده

    $user = User::factory()->create();

    $organizer = app(OrganizerRegistrationService::class)->register(
        user: $user,
        brandName: 'Cafe Lamiz',
        type: OrganizerType::Business,
        bio: 'کافه‌ای برای رویدادهای کوچک',
    );

    expect($organizer->status)->toBe(OrganizerStatus::Pending)
        ->and($organizer->slug)->toBe('cafe-lamiz')
        ->and($organizer->owner->id)->toBe($user->id);

    $membership = $organizer->members()->where('user_id', $user->id)->firstOrFail();
    expect($membership->role)->toBe(MemberRole::Owner)
        ->and($membership->status)->toBe(MemberStatus::Active)
        ->and($membership->joined_at)->not->toBeNull();

    expect(app(PermissionChecker::class)->userHasRole($user->id, 'organizer'))->toBeTrue();

    Event::assertDispatched(OrganizerRegistered::class, function (OrganizerRegistered $e) use ($organizer, $user): bool {
        return $e->organizerPublicId === $organizer->public_id
            && $e->ownerPublicId === $user->public_id
            && $e->brandName === 'Cafe Lamiz';
    });
});

it('rejects a second organizer for the same user', function (): void {
    Event::fake([OrganizerRegistered::class]);
    $user = User::factory()->create();
    $service = app(OrganizerRegistrationService::class);

    $service->register($user, 'First Brand', OrganizerType::Individual);

    $service->register($user, 'Second Brand', OrganizerType::Individual);
})->throws(OrganizerAlreadyExistsException::class);

it('resolves slug collisions with a numeric suffix', function (): void {
    Event::fake([OrganizerRegistered::class]);
    $service = app(OrganizerRegistrationService::class);

    $first = $service->register(User::factory()->create(), 'Cafe Lamiz', OrganizerType::Business);
    $second = $service->register(User::factory()->create(), 'Cafe Lamiz', OrganizerType::Business);

    expect($first->slug)->toBe('cafe-lamiz')
        ->and($second->slug)->toBe('cafe-lamiz-2');
});

it('falls back to a random slug for fully-Persian brand names', function (): void {
    Event::fake([OrganizerRegistered::class]);

    $organizer = app(OrganizerRegistrationService::class)->register(
        User::factory()->create(),
        'کافه لمیز',
        OrganizerType::Business,
    );

    expect($organizer->slug)->not->toBe('')
        ->and(strlen($organizer->slug))->toBeGreaterThanOrEqual(8);
});
