<?php

declare(strict_types=1);

use Modules\Identity\Enums\OrganizerStatus;

it('allows only legal transitions', function (
    OrganizerStatus $from, OrganizerStatus $to, bool $expected,
): void {
    expect($from->canTransitionTo($to))->toBe($expected);
})->with([
    'pending → active' => [OrganizerStatus::Pending, OrganizerStatus::Active, true],
    'pending → rejected' => [OrganizerStatus::Pending, OrganizerStatus::Rejected, true],
    'pending → suspended' => [OrganizerStatus::Pending, OrganizerStatus::Suspended, false],
    'active → suspended' => [OrganizerStatus::Active, OrganizerStatus::Suspended, true],
    'active → rejected' => [OrganizerStatus::Active, OrganizerStatus::Rejected, false],
    'suspended → active' => [OrganizerStatus::Suspended, OrganizerStatus::Active, true],
    'rejected → active' => [OrganizerStatus::Rejected, OrganizerStatus::Active, false],
    'rejected → pending' => [OrganizerStatus::Rejected, OrganizerStatus::Pending, false],
]);

it('permits event creation only when active', function (): void {
    expect(OrganizerStatus::Active->canCreateEvents())->toBeTrue()
        ->and(OrganizerStatus::Pending->canCreateEvents())->toBeFalse()
        ->and(OrganizerStatus::Suspended->canCreateEvents())->toBeFalse();
});
