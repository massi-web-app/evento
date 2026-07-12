<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Models\User;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Events\OrderStatusChanged;
use Modules\Orders\Exceptions\IllegalOrderTransitionException;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderTransitionService;
use Modules\Shared\ValueObjects\Money;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

/** موقت تا آمدن HoldService — بعداً به مسیر رسمی مهاجرت می‌کند */
function makeDraftOrder(): Order
{
    $event = makeEventFor();

    $order = new Order([
        'user_id' => User::factory()->create()->id,
        'event_id' => $event->id,
        'session_id' => $event->sessions()->firstOrFail()->id,
        'subtotal_amount' => Money::irr(500_000),
        'total_amount' => Money::irr(500_000),
    ]);
    $order->save();

    return $order->refresh();
}

it('walks pending → awaiting → paid and stamps paid_at once', function (): void {
    EventFacade::fake([OrderStatusChanged::class]);
    $order = makeDraftOrder();
    $service = app(OrderTransitionService::class);

    $service->transition($order, OrderStatus::AwaitingPayment);
    $service->transition($order, OrderStatus::Paid);

    $paidAt = $order->refresh()->paid_at;
    expect($order->status)->toBe(OrderStatus::Paid)
        ->and($paidAt)->not->toBeNull();

    EventFacade::assertDispatchedTimes(OrderStatusChanged::class, 2);
});

it('rejects illegal jumps', function (): void {
    $order = makeDraftOrder();   // Pending

    app(OrderTransitionService::class)->transition($order, OrderStatus::Refunded);
})->throws(IllegalOrderTransitionException::class);
