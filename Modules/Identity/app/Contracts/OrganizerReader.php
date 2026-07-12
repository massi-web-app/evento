<?php
declare(strict_types=1);

namespace Modules\Identity\Contracts;

interface OrganizerReader
{
    public function isActive(int $organizerId): bool;

    /** organizer فعالِ متعلق به این کاربر — null اگر ندارد */
    public function activeOrganizerIdForUser(int $userId): ?int;

}
