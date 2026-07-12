<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use Modules\Identity\Contracts\OrganizerReader;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Models\Organizer;

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
}
