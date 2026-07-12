<?php

declare(strict_types=1);

use Modules\Orders\Enums\OrderStatus;

it('allows only legal transitions', function (OrderStatus $from, OrderStatus $to, bool $expected): void {
    expect($from->canTransitionTo($to))->toBe($expected);
})->with([
    'pending → awaiting'          => [OrderStatus::Pending, OrderStatus::AwaitingPayment, true],
    'pending → paid (skip)'       => [OrderStatus::Pending, OrderStatus::Paid, false],
    'awaiting → paid'             => [OrderStatus::AwaitingPayment, OrderStatus::Paid, true],
    'awaiting → expired'          => [OrderStatus::AwaitingPayment, OrderStatus::Expired, true],
    'paid → refunded'             => [OrderStatus::Paid, OrderStatus::Refunded, true],
    'paid → canceled (illegal)'   => [OrderStatus::Paid, OrderStatus::Canceled, false],
    'partial → refunded'          => [OrderStatus::PartiallyRefunded, OrderStatus::Refunded, true],
    'expired → anything'          => [OrderStatus::Expired, OrderStatus::AwaitingPayment, false],
    'refunded → anything'         => [OrderStatus::Refunded, OrderStatus::Paid, false],
]);

it('marks capacity-holding statuses correctly', function (): void {
    expect(OrderStatus::Pending->holdsCapacity())->toBeTrue()
        ->and(OrderStatus::AwaitingPayment->holdsCapacity())->toBeTrue()
        ->and(OrderStatus::Paid->holdsCapacity())->toBeFalse()
        ->and(OrderStatus::Expired->holdsCapacity())->toBeFalse();
});
