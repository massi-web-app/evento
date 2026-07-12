<?php

declare(strict_types=1);

namespace Modules\Events\Services;

use Modules\Events\Enums\EventStatus;
use Modules\Events\Events\EventStatusChanged;
use Modules\Events\Exceptions\IllegalEventTransitionException;
use Modules\Events\Models\Event;

final class EventTransitionService
{
    public function transition(Event $event, EventStatus $to, ?int $actorUserId = null, ?string $reason = null): Event
    {

        $from = $event->status;
        if (!$from->canTransitionTo($to)) {
            throw IllegalEventTransitionException::between($from, $to);
        }

        $event->forceFill([
            'status' => $to,
            'published_at' => $to === EventStatus::Published && $event->published_at === null
                ? now()
                : $event->published_at,
        ])->save();

        event(new EventStatusChanged(
            eventPublicId: $event->public_id,
            from: $from->value,
            to: $to->value,
            actorUserId: $actorUserId,
            reason: $reason,
        ));

        return $event;
    }

}
