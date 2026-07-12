<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Events\Enums\EventStatus;
use Modules\Events\Services\EventTransitionService;
use Modules\Identity\Database\Seeders\RbacSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

function publishEvent(\Modules\Events\Models\Event $event): void
{
    $t = app(EventTransitionService::class);
    $t->transition($event, EventStatus::PendingReview);
    $t->transition($event, EventStatus::Approved);
    $t->transition($event, EventStatus::Published);
}

it('lists only published upcoming events', function (): void {
    $published = makeEventFor();
    publishEvent($published);

    makeEventFor();   // draft — نباید بیاید

    $response = $this->getJson(route('api.public.events.index'));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($published->public_id);
});

it('filters by category', function (): void {
    $target = makeEventFor();
    publishEvent($target);
    $other = makeEventFor();
    publishEvent($other);

    $response = $this->getJson(route('api.public.events.index', [
        'category_id' => $target->category_id,
    ]));

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe($target->public_id);
});

it('paginates with a stable cursor', function (): void {
    foreach (range(1, 25) as $i) {
        publishEvent(makeEventFor(startsAt: now()->addDays(10 + $i)->toImmutable()));
    }

    $first = $this->getJson(route('api.public.events.index'));
    expect($first->json('data'))->toHaveCount(20)
        ->and($first->json('meta.next_cursor'))->not->toBeNull();

    $second = $this->getJson(route('api.public.events.index', [
        'cursor' => $first->json('meta.next_cursor'),
    ]));

    expect($second->json('data'))->toHaveCount(5);

    $ids = [...array_column($first->json('data'), 'id'), ...array_column($second->json('data'), 'id')];
    expect($ids)->toHaveCount(25)->and(array_unique($ids))->toHaveCount(25);   // بدون تکرار/جاافتادگی
});

it('rejects an invalid filter with 422', function (): void {
    $this->getJson(route('api.public.events.index', ['city_id' => 999999]))
        ->assertStatus(422);
});
