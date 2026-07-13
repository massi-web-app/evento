<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Events\Contracts\SellableTicketTypes;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Models\User;
use Modules\Orders\Contracts\CapacityCounter;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Services\HoldExpiryService;
use Modules\Orders\Services\HoldService;
use Modules\Settings\Database\Seeders\SettingDefinitionsSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(SettingDefinitionsSeeder::class);
});

it('expires an overdue hold and releases capacity for the next buyer', function (): void {
    $tt = makeOnSaleTicketType(capacity: 1);
    $holdService = app(HoldService::class);
    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);

    $order = $holdService->hold(User::factory()->create()->id, $sellable, 1);

    /** @phpstan-ignore method.notFound (Pest binds $this to Laravel TestCase at runtime) */
    $this->travel(20)->minutes();

    $count = app(HoldExpiryService::class)->expireOverdue();

    expect($count)->toBe(1)
        ->and($order->refresh()->status)->toBe(OrderStatus::Expired);

    $second = $holdService->hold(
        User::factory()->create()->id,
        app(SellableTicketTypes::class)->byPublicId($tt->public_id),
        1,
    );
    expect($second->status)->toBe(OrderStatus::Pending);
});

it('leaves fresh holds untouched', function (): void {
    $tt = makeOnSaleTicketType();
    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);
    app(HoldService::class)->hold(User::factory()->create()->id, $sellable, 1);

    expect(app(HoldExpiryService::class)->expireOverdue())->toBe(0);
});

it('is idempotent — a second sweep expires nothing new', function (): void {
    $tt = makeOnSaleTicketType();
    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);
    app(HoldService::class)->hold(User::factory()->create()->id, $sellable, 1);

    /** @phpstan-ignore method.notFound (Pest binds $this to Laravel TestCase at runtime) */
    $this->travel(20)->minutes();

    $service = app(HoldExpiryService::class);

    expect($service->expireOverdue())->toBe(1)
        ->and($service->expireOverdue())->toBe(0);

    $counter = app(CapacityCounter::class);
    $key = app(HoldService::class)->counterKey($tt->id);
    expect($counter->tryAcquire($key, 10))->toBeTrue()
        ->and($counter->tryAcquire($key, 1))->toBeFalse();
});
