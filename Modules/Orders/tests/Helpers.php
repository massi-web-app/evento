<?php

declare(strict_types=1);

use Modules\Events\Contracts\SellableTicketTypes;
use Modules\Events\Models\TicketType;
use Modules\Identity\Models\User;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\HoldService;
use Modules\Shared\ValueObjects\Money;

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

function heldOrder(int $quantity = 2): Order
{
    $tt = makeOnSaleTicketType();

    $sellable = app(SellableTicketTypes::class)->byPublicId($tt->public_id);

    return app(HoldService::class)->hold(
        userId: User::factory()->create()->id,
        ticketType: $sellable,
        quantity: $quantity,
    );
}
