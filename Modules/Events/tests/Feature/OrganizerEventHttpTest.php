<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Catalog\Models\Category;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Enums\EventStatus;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Enums\MemberRole;
use Modules\Identity\Enums\MemberStatus;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Models\Organizer;
use Modules\Identity\Models\User;
use Modules\Identity\Services\OrganizerRegistrationService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

/** @return array{0: User, 1: Organizer} */
function ownerWithActiveOrganizer(): array
{
    $owner = User::factory()->create();
    $organizer = app(OrganizerRegistrationService::class)
        ->register($owner, fake()->company(), OrganizerType::Business);
    $organizer->forceFill(['status' => OrganizerStatus::Active])->save();

    return [$owner, $organizer];
}

/** @return array<string, mixed> */
function validEventPayload(): array
{
    return [
        'title' => 'کنسرت تابستانی',
        'category_id' => Category::factory()->create()->id,
        'format' => EventFormat::Online->value,
        'starts_at' => now()->addDays(15)->toIso8601String(),
        'ends_at' => now()->addDays(15)->addHours(2)->toIso8601String(),
        'capacity_total' => 300,
    ];
}

it('lets an active organizer owner create a draft event', function (): void {
    [$owner] = ownerWithActiveOrganizer();
    Sanctum::actingAs($owner);

    $response = $this->postJson(route('api.organizer.events.store'), validEventPayload());



    $response->assertStatus(201)
        ->assertJsonPath('data.status', EventStatus::Draft->value)
        ->assertJsonMissingPath('data.organizer_id');

    expect($response->json('data.id'))->toHaveLength(26);
});

it('returns 403 for a user without an active organizer', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.organizer.events.store'), validEventPayload())
        ->assertStatus(403);
});

it('maps the venue-required domain guard to 422', function (): void {
    [$owner] = ownerWithActiveOrganizer();
    Sanctum::actingAs($owner);

    $this->postJson(route('api.organizer.events.store'), [
        ...validEventPayload(),
        'format' => EventFormat::InPerson->value,
        'venue_id' => null,
    ])->assertStatus(422);
});

it('allows a manager member of the same organizer to submit for review', function (): void {
    [$owner, $organizer] = ownerWithActiveOrganizer();
    Sanctum::actingAs($owner);
    $storeResponse = $this->postJson(route('api.organizer.events.store'), validEventPayload());
    $storeResponse->assertStatus(201);
    $eventId = $storeResponse->json('data.id');



    $manager = User::factory()->create();
    $organizer->members()->create([
        'user_id' => $manager->id,
        'role' => MemberRole::Manager,
        'status' => MemberStatus::Active,
        'joined_at' => now(),
    ]);

    Sanctum::actingAs($manager);

    $this->postJson(route('api.organizer.events.submit', ['event' => $eventId]))
        ->assertStatus(200)
        ->assertJsonPath('data.status', EventStatus::PendingReview->value);


});

it('blocks a stranger from submitting someone else’s event', function (): void {
    [$owner] = ownerWithActiveOrganizer();
    Sanctum::actingAs($owner);
    $eventId = $this->postJson(route('api.organizer.events.store'), validEventPayload())
        ->json('data.id');

    Sanctum::actingAs(User::factory()->create());   // غریبهٔ لاگین‌شده

    $this->postJson(route('api.organizer.events.submit', ['event' => $eventId]))
        ->assertStatus(403);
});
