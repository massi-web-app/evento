<?php

declare(strict_types=1);

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Events\Enums\EventStatus;
use Modules\Events\Http\Requests\CreateEventRequest;
use Modules\Events\Http\Resources\EventResource;
use Modules\Events\Models\Event;
use Modules\Events\Services\EventCreationService;
use Modules\Events\Services\EventTransitionService;
use Modules\Identity\Contracts\OrganizerReader;

final class OrganizerEventController extends Controller
{
    use AuthorizesRequests;

    public function store(CreateEventRequest $request, EventCreationService $creation, OrganizerReader $organizerReader): JsonResponse
    {
        $organizerId = $organizerReader->activeOrganizerIdForUser(
            (int)$request->user()->getAuthIdentifier(),
        );

        abort_if($organizerId === null, 403, 'برای ساخت رویداد باید پروفایل سازندهٔ فعال داشته باشید.');

        $event = $creation->create(
            organizerId: $organizerId,
            categoryId: (int)$request->validated('category_id'),
            title: (string)$request->validated('title'),
            format: $request->eventFormat(),
            startsAt: $request->starsAt(),
            endsAt: $request->endsAt(),
            venueId: $request->validated('venue_id') !== null ? (int)$request->validated('venue_id') : null,
            cityId: $request->validated('city_id') !== null ? (int)$request->validated('city_id') : null,
            summary: $request->validated('summary'),
            capacityTotal: $request->validated('capacity_total') !== null ? (int)$request->validated('capacity_total') : null,
        );


        return EventResource::make($event)->response()->setStatusCode(201);

    }

    public function submitForReview(
        Event                  $event,
        EventTransitionService $transition,
    ): JsonResponse
    {
        $this->authorize('transition', $event);

        $transition->transition(
            $event,
            EventStatus::PendingReview,
            actorUserId: (int)auth()->id(),
        );

        return EventResource::make($event->refresh())->response();
    }

}
