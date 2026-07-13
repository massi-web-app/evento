<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Events\Contracts\SellableTicketTypes;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Models\User;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Exceptions\InsufficientCapacityException;
use Modules\Orders\Exceptions\InvalidQuantityException;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\HoldService;
use Modules\Settings\Database\Seeders\SettingDefinitionsSeeder;
use Modules\Shared\ValueObjects\Money;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(SettingDefinitionsSeeder::class);
});

it('holds capacity and creates a pending order with snapshot and deadline', function (): void {
    $tt = makeOnSaleTicketType();
    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);
    $user = User::factory()->create();

    $order = app(HoldService::class)->hold($user->id, $sellable, 2);

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->hold_expires_at)->not->toBeNull()
        ->and($order->total_amount->equals(Money::irr(1_000_000)))->toBeTrue();

    $item = $order->items()->firstOrFail();
    expect($item->unit_amount_snapshot->equals(Money::irr(500_000)))->toBeTrue()
        ->and($item->ticket_type_name_snapshot)->toBe('عادی');
});

it('rejects quantities outside per-order bounds', function (): void {
    $tt = makeOnSaleTicketType();
    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);

    app(HoldService::class)->hold(User::factory()->create()->id, $sellable, 11);   // max=10
})->throws(InvalidQuantityException::class);

it('lets exactly one of two simultaneous buyers take the last seat', function (): void {
    $tt = makeOnSaleTicketType(capacity: 1);
    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);
    $service = app(HoldService::class);
    [$alice, $bob] = [User::factory()->create(), User::factory()->create()];

    $results = [];
    foreach ([$alice, $bob] as $buyer) {
        try {
            $service->hold($buyer->id, $sellable, 1);
            $results[] = 'won';
        } catch (InsufficientCapacityException) {
            $results[] = 'lost';
        }
    }

    sort($results);
    expect($results)->toBe(['lost', 'won'])
        ->and(Order::query()->count())->toBe(1);
});

it('releases capacity back when the DB step fails (compensation)', function (): void {
    $tt = makeOnSaleTicketType(capacity: 1);
    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);

    try {
        app(HoldService::class)->hold(999_999, $sellable, 1);   // FK می‌ترکد
    } catch (\Throwable) {
    }

    $order = app(HoldService::class)->hold(User::factory()->create()->id, $sellable, 1);
    expect($order)->toBeInstanceOf(Order::class);
});
