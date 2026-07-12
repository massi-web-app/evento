<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Modules\Events\Enums\EventStatus;
use Modules\Events\Events\EventStatusChanged;
use Modules\Events\Exceptions\IllegalEventTransitionException;
use Modules\Events\Services\EventTransitionService;
use Modules\Identity\Database\Seeders\RbacSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

it('walks the happy path draft → published and stamps published_at once', function (): void {
    EventFacade::fake([EventStatusChanged::class]);
    $event = makeEventFor();
    $service = app(EventTransitionService::class);

    $service->transition($event, EventStatus::PendingReview);
    $service->transition($event, EventStatus::Approved);
    $service->transition($event, EventStatus::Published, actorUserId: 1);

    $publishedAt = $event->refresh()->published_at;
    expect($event->status)->toBe(EventStatus::Published)
        ->and($publishedAt)->not->toBeNull();

    // pause → resume نباید published_at را عوض کند
    $service->transition($event, EventStatus::Paused);
    $service->transition($event, EventStatus::Published);

    expect($event->refresh()->published_at->equalTo($publishedAt))->toBeTrue();

    EventFacade::assertDispatchedTimes(EventStatusChanged::class, 5);
});

it('rejects illegal jumps', function (): void {
    $event = makeEventFor();   // Draft

    app(EventTransitionService::class)->transition($event, EventStatus::Published);
})->throws(IllegalEventTransitionException::class);
