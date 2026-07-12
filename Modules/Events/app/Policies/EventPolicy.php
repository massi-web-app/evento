<?php


declare(strict_types=1);

namespace Modules\Events\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Events\Models\Event;
use Modules\Identity\Contracts\OrganizerReader;

final readonly class EventPolicy
{

    private const array MANAGING_ROLES = [1, 2, 3];

    public function __construct(
        private OrganizerReader $organizerReader,
    ) {}
    public function update(Authenticatable $user, Event $event): bool
    {
        return $this->managesOrganizer($user, $event->organizer_id);
    }

    public function transition(Authenticatable $user, Event $event): bool
    {
        return $this->managesOrganizer($user, $event->organizer_id);
    }

    public function manageTickets(Authenticatable $user, Event $event): bool
    {
        return $this->managesOrganizer($user, $event->organizer_id);
    }

    private function managesOrganizer(Authenticatable $user, int $organizerId): bool
    {
        return $this->organizerReader->userHasMembership(
            userId: (int) $user->getAuthIdentifier(),
            organizerId: $organizerId,
            roles: self::MANAGING_ROLES,
        );
    }



}
