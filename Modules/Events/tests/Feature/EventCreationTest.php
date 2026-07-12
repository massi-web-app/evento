<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Exceptions\OrganizerNotActiveException;
use Modules\Events\Exceptions\VenueRequiredException;
use Modules\Events\Services\EventCreationService;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Models\User;
use Modules\Identity\Services\OrganizerRegistrationService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

it('creates a draft event with its default session', function (): void {
    $organizer = app(OrganizerRegistrationService::class)
        ->register(User::factory()->create(), 'برند فعال', OrganizerType::Business);
    $organizer->forceFill(['status' => OrganizerStatus::Active])->save();

    $event = app(EventCreationService::class)->create(
        organizerId: $organizer->id,
        categoryId: Category::factory()->create()->id,
        title: 'کنسرت بهاری',
        format: EventFormat::Online,
        startsAt: CarbonImmutable::now()->addDays(20),
        endsAt: CarbonImmutable::now()->addDays(20)->addHours(2),
    );

    expect($event->sessions()->count())->toBe(1)
        ->and($event->sessions()->first()->starts_at->equalTo($event->starts_at))->toBeTrue();
});

it('blocks non-active organizers', function (): void {
    $organizer = app(OrganizerRegistrationService::class)
        ->register(User::factory()->create(), 'برند معلق', OrganizerType::Business);
    // status هنوز Pending

    app(EventCreationService::class)->create(
        organizerId: $organizer->id,
        categoryId: Category::factory()->create()->id,
        title: 'رویداد ممنوع',
        format: EventFormat::Online,
        startsAt: CarbonImmutable::now()->addDay(),
        endsAt: CarbonImmutable::now()->addDay()->addHour(),
    );
})->throws(OrganizerNotActiveException::class);

it('requires a venue for in-person events', function (): void {
    $organizer = app(OrganizerRegistrationService::class)
        ->register(User::factory()->create(), 'برند حضوری', OrganizerType::Business);
    $organizer->forceFill(['status' => OrganizerStatus::Active])->save();

    app(EventCreationService::class)->create(
        organizerId: $organizer->id,
        categoryId: Category::factory()->create()->id,
        title: 'کارگاه حضوری',
        format: EventFormat::InPerson,
        startsAt: CarbonImmutable::now()->addDay(),
        endsAt: CarbonImmutable::now()->addDay()->addHour(),
        venueId: null,
    );
})->throws(VenueRequiredException::class);
