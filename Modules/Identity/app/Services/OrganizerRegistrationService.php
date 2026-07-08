<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Enums\MemberRole;
use Modules\Identity\Enums\MemberStatus;
use Modules\Identity\Enums\OrganizerStatus;
use Modules\Identity\Enums\OrganizerType;
use Modules\Identity\Events\OrganizerRegistered;
use Modules\Identity\Exceptions\OrganizerAlreadyExistsException;
use Modules\Identity\Models\Organizer;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;

final readonly class OrganizerRegistrationService
{
    public function __construct(private RoleAssignmentService $roleAssignment) {}

    public function register(User $user, string $brandName, OrganizerType $type, ?string $bio = null): Organizer
    {

        if ($user->organizers()->exists()) {
            throw OrganizerAlreadyExistsException::forUser($user->public_id);
        }

        $organizer = DB::transaction(function () use ($user, $brandName, $type, $bio): Organizer {
            $organizer = new Organizer([
                'brand_name' => $brandName,
                'type' => $type,
                'bio' => $bio,
                'slug' => $this->uniqueSlug($brandName),
            ]);
            $organizer->owner()->associate($user);
            $organizer->forceFill(['status' => OrganizerStatus::Pending])->save();

            $organizer->members()->create([
                'user_id' => $user->id,
                'role' => MemberRole::Owner,
                'status' => MemberStatus::Active,
                'joined_at' => now(),
            ]);

            $this->roleAssignment->assign(
                $user,
                Role::query()->where('name', 'organizer')->firstOrFail(),
            );

            return $organizer;
        });

        event(new OrganizerRegistered(
            organizerPublicId: $organizer->public_id,
            ownerPublicId: $user->public_id,
            brandName: $brandName,
        ));

        return $organizer;

    }

    private function uniqueSlug(string $brandName): string
    {
        $base = Str::slug($brandName) ?: Str::lower(Str::random(8));
        $slug = $base;

        for ($i = 2; Organizer::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
