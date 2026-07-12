<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Events\Models\EventSession;
use Modules\Events\Models\TicketType;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Shared\ValueObjects\Money;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
});

function makeTicketTypeWithPrices(): Model
{
    $event = makeEventFor();

    /** @var EventSession $session */
    $session = $event->sessions()->firstOrFail();

    $ticketType = $session->ticketTypes()->make([
        'name' => 'عادی',
        'capacity' => 100,
    ]);
    $ticketType->save();

    $ticketType->prices()->createMany([
        [
            'label' => 'پیش‌فروش',
            'amount' => Money::irr(400_000),
            'currency' => 'IRR',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(5),
        ],
        [
            'label' => 'عادی',
            'amount' => Money::irr(500_000),
            'currency' => 'IRR',
            'starts_at' => now()->addDays(5),
            'ends_at' => null,
        ],
    ]);

    return $ticketType->refresh()->load('prices');
}

it('returns the early-bird price during its window as Money', function (): void {
    $ticketType = makeTicketTypeWithPrices();

    $price = $ticketType->currentPrice();

    expect($price)->toBeInstanceOf(Money::class)
        ->and($price->equals(Money::irr(400_000)))->toBeTrue();
});

it('switches to the regular price after the window', function (): void {
    $ticketType = makeTicketTypeWithPrices();

    $future = CarbonImmutable::now()->addDays(6);

    expect($ticketType->currentPrice($future)->equals(Money::irr(500_000)))->toBeTrue();
});

it('returns null before any window opens', function (): void {
    $ticketType = makeTicketTypeWithPrices();

    $past = CarbonImmutable::now()->subDays(30);

    expect($ticketType->currentPrice($past))->toBeNull();
});

it('prefers the newest window on overlap', function (): void {
    $ticketType = makeTicketTypeWithPrices();
    $ticketType->prices()->create([
        'label' => 'تخفیف لحظه‌ای',
        'amount' => Money::irr(350_000),
        'currency' => 'IRR',
        'starts_at' => now()->subHour(),   // همپوشان با پیش‌فروش، جدیدتر
        'ends_at' => now()->addDay(),
    ]);
    $ticketType->refresh()->load('prices');

    expect($ticketType->currentPrice()->equals(Money::irr(350_000)))->toBeTrue();
});
