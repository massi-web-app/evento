<?php

declare(strict_types=1);

use Modules\Catalog\Models\Category;
use Modules\Events\Models\Event;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Models\User;
use Modules\Identity\Services\OrganizerRegistrationService;

/**
 * ساخت event تستی با زنجیرهٔ کامل وابستگی‌ها — از مسیرهای رسمی هر ماژول.
 *
 * @param array<string, mixed> $overrides
 */
function makeEventFor(array $overrides = []): \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
{
    $organizer = app(OrganizerRegistrationService::class)->register(
        user: User::factory()->create(),
        brandName: fake()->company(),
        type: OrganizerType::Business,
    );

    $category = Category::factory()->create();

    $event = Event::factory()->make($overrides);
    $event->forceFill([
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
    ])->save();

    return $event->refresh();
}
