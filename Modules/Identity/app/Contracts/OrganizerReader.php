<?php
declare(strict_types=1);

namespace Modules\Identity\Contracts;

interface OrganizerReader
{
    public function isActive(int $organizerId): bool;

    /** organizer فعالِ متعلق به این کاربر — null اگر ندارد */
    public function activeOrganizerIdForUser(int $userId): ?int;

    /** آیا کاربر عضو فعالِ این organizer با یکی از این نقش‌هاست؟
     * @param list<int> $roles مقادیر MemberRole
     */
    public function userHasMembership(int $userId, int $organizerId, array $roles): bool;

}
