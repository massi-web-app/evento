<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Events\Models\TicketType;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Models\User;
use Modules\Orders\Contracts\CapacityCounter;
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

function makeOnSaleTicketType(int $capacity = 10): TicketType
{
    $event = makeEventFor();
    $session = $event->sessions()->firstOrFail();

    $tt = $session->ticketTypes()->make(['name' => 'عادی', 'capacity' => $capacity]);
    $tt->save();
    $tt->prices()->create([
        'amount' => Money::irr(500_000),
        'starts_at' => now()->subDay(),
        'ends_at' => null,
    ]);

    return $tt->refresh()->load('prices', 'session');
}

it('holds capacity and creates a pending order with snapshot and deadline', function (): void {
    $tt = makeOnSaleTicketType();
    $user = User::factory()->create();

    $order = app(HoldService::class)->hold($user->id, $tt, 2);

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->hold_expires_at)->not->toBeNull()
        ->and($order->total_amount->equals(Money::irr(1_000_000)))->toBeTrue();

    $item = $order->items()->firstOrFail();
    expect($item->unit_amount_snapshot->equals(Money::irr(500_000)))->toBeTrue()
        ->and($item->ticket_type_name_snapshot)->toBe('عادی');
});

it('rejects quantities outside per-order bounds', function (): void {
    $tt = makeOnSaleTicketType();

    app(HoldService::class)->hold(User::factory()->create()->id, $tt, 11);   // max=10
})->throws(InvalidQuantityException::class);

it('lets exactly one of two simultaneous buyers take the last seat', function (): void {
    $tt = makeOnSaleTicketType(capacity: 1);
    $service = app(HoldService::class);
    [$alice, $bob] = [User::factory()->create(), User::factory()->create()];

    $results = [];
    foreach ([$alice, $bob] as $buyer) {
        try {
            $service->hold($buyer->id, $tt, 1);
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
    $counter = app(CapacityCounter::class);
    $key = app(HoldService::class)->counterKey($tt->id);

    // شکست DB را با user_id ناموجود مهندسی می‌کنیم — FK می‌ترکد
    try {
        app(HoldService::class)->hold(999_999, $tt, 1);
    } catch (\Throwable) {
    }

    // جبران باید ظرفیت را برگردانده باشد — خریدار واقعی هنوز می‌تواند بخرد
    $order = app(HoldService::class)->hold(User::factory()->create()->id, $tt, 1);
    expect($order)->toBeInstanceOf(Order::class);
});
