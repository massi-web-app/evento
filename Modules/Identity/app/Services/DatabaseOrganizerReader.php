<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use Modules\Identity\Contracts\OrganizerReader;
use Modules\Identity\Enums\MemberStatus;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Models\Organizer;
use Modules\Identity\Models\OrganizerMember;

final class DatabaseOrganizerReader implements OrganizerReader
{

    public function isActive(int $organizerId): bool
    {
        return Organizer::query()
            ->whereKey($organizerId)
            ->where('status', OrganizerStatus::Active)
            ->exists();
    }

    public function activeOrganizerIdForUser(int $userId): ?int
    {
        return Organizer::query()
            ->where('owner_user_id', $userId)
            ->where('status', OrganizerStatus::Active)
            ->value('id');
    }

    public function userHasMembership(int $userId, int $organizerId, array $roles): bool
    {
        return OrganizerMember::query()
            ->where('organizer_id', $organizerId)
            ->where('user_id', $userId)
            ->where('status', MemberStatus::Active)
            ->whereIn('role', $roles)
            ->exists();
    }
}
