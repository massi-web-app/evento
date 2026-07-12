<?php

declare(strict_types=1);

use Modules\Events\Enums\EventStatus;

it('allows only legal transitions', function (EventStatus $from, EventStatus $to, bool $expected): void {
    expect($from->canTransitionTo($to))->toBe($expected);
})->with([
    'draft → pending_review'      => [EventStatus::Draft, EventStatus::PendingReview, true],
    'draft → published (skip)'    => [EventStatus::Draft, EventStatus::Published, false],
    'pending → approved'          => [EventStatus::PendingReview, EventStatus::Approved, true],
    'pending → rejected'          => [EventStatus::PendingReview, EventStatus::Rejected, true],
    'pending → published (skip)'  => [EventStatus::PendingReview, EventStatus::Published, false],
    'approved → published'        => [EventStatus::Approved, EventStatus::Published, true],
    'published → paused'          => [EventStatus::Published, EventStatus::Paused, true],
    'published → canceled'        => [EventStatus::Published, EventStatus::Canceled, true],
    'published → draft (back)'    => [EventStatus::Published, EventStatus::Draft, false],
    'paused → published (resume)' => [EventStatus::Paused, EventStatus::Published, true],
    'ended → anything'            => [EventStatus::Ended, EventStatus::Published, false],
    'canceled → anything'         => [EventStatus::Canceled, EventStatus::Published, false],
    'rejected → anything'         => [EventStatus::Rejected, EventStatus::PendingReview, false],
]);

it('exposes visibility and sellability correctly', function (): void {
    expect(EventStatus::Published->isSellable())->toBeTrue()
        ->and(EventStatus::Paused->isSellable())->toBeFalse()
        ->and(EventStatus::Paused->isPubliclyVisible())->toBeTrue()
        ->and(EventStatus::Draft->isPubliclyVisible())->toBeFalse()
        ->and(EventStatus::Ended->isPubliclyVisible())->toBeTrue();
});
