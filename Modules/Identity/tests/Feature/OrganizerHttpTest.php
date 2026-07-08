<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Models\User;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

it('registers an organizer over HTTP and hides internal ids', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson(route('api.organizers.store'), [
        'brand_name' => 'Cafe Lamiz',
        'type' => OrganizerType::Business->value,
        'bio' => 'رویدادهای کافه‌ای',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.brand_name', 'Cafe Lamiz')
        ->assertJsonPath('data.status_label', 'در انتظار بررسی')
        ->assertJsonMissingPath('data.owner_user_id');

    expect($response->json('data.id'))->toHaveLength(26);   // ULID، نه id عددی
});

it('requires authentication', function (): void {
    $this->postJson(route('api.organizers.store'), [
        'brand_name' => 'Ghost Brand',
        'type' => OrganizerType::Individual->value,
    ])->assertStatus(401);
});

it('returns 409 for a user who already owns an organizer', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = ['brand_name' => 'First', 'type' => OrganizerType::Individual->value];

    $this->postJson(route('api.organizers.store'), $payload)->assertStatus(201);
    $this->postJson(route('api.organizers.store'), ['brand_name' => 'Second', 'type' => OrganizerType::Individual->value])
        ->assertStatus(409);
});

it('validates the enum-backed type field', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.organizers.store'), [
        'brand_name' => 'Bad Type Brand',
        'type' => 99,
    ])->assertStatus(422)->assertJsonValidationErrors('type');
});
