<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Modules\Catalog\Models\Category;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Models\Event;
use Modules\Events\Services\EventCreationService;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Models\User;
use Modules\Identity\Services\OrganizerRegistrationService;

/**
 * ساخت event تستی از مسیر رسمی — با session پیش‌فرض و زنجیرهٔ کامل.
 */
function makeEventFor(
    ?EventFormat $format = null,
    ?CarbonImmutable $startsAt = null,
): Event {
    $organizer = app(OrganizerRegistrationService::class)->register(
        user: User::factory()->create(),
        brandName: fake()->company(),
        type: OrganizerType::Business,
    );
    $organizer->forceFill(['status' => OrganizerStatus::Active])->save();

    $startsAt ??= CarbonImmutable::now()->addDays(30);

    return app(EventCreationService::class)->create(
        organizerId: $organizer->id,
        categoryId: Category::factory()->create()->id,
        title: fake()->unique()->sentence(3),
        format: $format ?? EventFormat::Online,
        startsAt: $startsAt,
        endsAt: $startsAt->addHours(3),
    );
}
