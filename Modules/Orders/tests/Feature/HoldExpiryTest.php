<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $order = $holdService->hold(User::factory()->create()->id, $tt, 1);

    /** @phpstan-ignore method.notFound (Pest binds $this to Laravel TestCase at runtime) */
    $this->travel(20)->minutes();   // مهلت ۱۵ دقیقه گذشت

    $count = app(HoldExpiryService::class)->expireOverdue();

    expect($count)->toBe(1)
        ->and($order->refresh()->status)->toBe(OrderStatus::Expired);

    // صندلی واقعاً برگشته؟ خریدار بعدی باید بتواند بگیرد
    $second = $holdService->hold(User::factory()->create()->id, $tt->refresh()->load('prices', 'session'), 1);
    expect($second->status)->toBe(OrderStatus::Pending);
});

it('leaves fresh holds untouched', function (): void {
    $tt = makeOnSaleTicketType();
    app(HoldService::class)->hold(User::factory()->create()->id, $tt, 1);

    expect(app(HoldExpiryService::class)->expireOverdue())->toBe(0);
});

it('is idempotent — a second sweep expires nothing new', function (): void {
    $tt = makeOnSaleTicketType();
    app(HoldService::class)->hold(User::factory()->create()->id, $tt, 1);

    /** @phpstan-ignore method.notFound (Pest binds $this to Laravel TestCase at runtime) */
    $this->travel(20)->minutes();

    $service = app(HoldExpiryService::class);

    expect($service->expireOverdue())->toBe(1)
        ->and($service->expireOverdue())->toBe(0);   // دور دوم: هیچ

    // و ظرفیت فقط یک‌بار برگشته — نه دوبار (double-release یعنی oversell)
    $counter = app(CapacityCounter::class);
    $key = app(HoldService::class)->counterKey($tt->id);
    expect($counter->tryAcquire($key, 10))->toBeTrue()      // کل ۱۰ تا هست
    ->and($counter->tryAcquire($key, 1))->toBeFalse();  // یازدهمی نه
});
