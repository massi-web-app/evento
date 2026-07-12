<?php

declare(strict_types=1);

namespace Modules\Events\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Events\EventDrafted;
use Modules\Events\Exceptions\InvalidEventScheduleException;
use Modules\Events\Exceptions\OrganizerNotActiveException;
use Modules\Events\Exceptions\VenueRequiredException;
use Modules\Events\Models\Event;
use Modules\Identity\Contracts\OrganizerReader;

final readonly class EventCreationService
{
    public function __construct(
        private OrganizerReader $organizerReader,
    )
    {
    }

    public function create(int             $organizerId, int $categoryId, string $title, EventFormat $format, CarbonImmutable $startsAt,
                           CarbonImmutable $endsAt, ?int $venueId = null, ?int $cityId = null, ?string $summary = null, ?int $capacityTotal = null):Event
    {
        if (!$this->organizerReader->isActive($organizerId)) {
            throw  OrganizerNotActiveException::forId($organizerId);
        }

        if ($format->requiresVenue() && $venueId === null) {
            throw VenueRequiredException::forFormat($format);
        }

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw InvalidEventScheduleException::endsBeforeStarts();
        }

        $event = DB::transaction(function () use (
            $organizerId, $categoryId, $title, $format, $startsAt, $endsAt, $venueId, $cityId, $summary, $capacityTotal,
        ): Event {
            $event = new Event([
                'category_id' => $categoryId,
                'venue_id' => $venueId,
                'city_id' => $cityId,
                'title' => $title,
                'slug' => $this->uniqueSlug($title),
                'summary' => $summary,
                'format' => $format,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'capacity_total' => $capacityTotal,
            ]);
            $event->forceFill(['organizer_id' => $organizerId])->save();

            // session پیش‌فرض — قرارداد «حتی تک‌اجرا یک session دارد»
            $event->refresh();
            $session = $event->sessions()->make([
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'capacity' => $capacityTotal,
            ]);
            $session->save();

            return $event;
        });

        event(new EventDrafted(
            eventPublicId: $event->public_id,
            organizerId: $organizerId,
        ));

        return $event;


    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;

        for ($i = 2; Event::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }


}
