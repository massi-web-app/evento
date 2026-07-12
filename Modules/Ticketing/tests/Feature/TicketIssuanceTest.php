<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Orders\Services\PaymentService;
use Modules\Settings\Database\Seeders\SettingDefinitionsSeeder;
use Modules\Ticketing\Enums\TicketStatus;
use Modules\Ticketing\Exceptions\OversellDetectedException;
use Modules\Ticketing\Models\Ticket;
use Modules\Ticketing\Services\TicketIssuanceService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(SettingDefinitionsSeeder::class);
});

it('issues tickets end-to-end after a verified payment', function (): void {
    $order = heldOrder();   // qty=2
    $service = app(PaymentService::class);
    $payment = $service->initiate($order, 'https://evento.test/callback');

    $service->handleCallback($payment->gateway_token);   // sync queue → listener inline

    $tickets = Ticket::query()->get();

    expect($tickets)->toHaveCount(2)
        ->and($tickets->pluck('checkin_code')->unique())->toHaveCount(2)
        ->and($tickets->first()->status)->toBe(TicketStatus::Issued)
        ->and($tickets->first()->holder_user_id)->toBe($order->user_id)
        ->and(DB::table('ticket_types')->value('sold_cache'))->toBe(2);
});

it('is idempotent when the queue delivers twice', function (): void {
    $order = heldOrder();
    $service = app(PaymentService::class);
    $payment = $service->initiate($order, 'https://evento.test/callback');
    $service->handleCallback($payment->gateway_token);

    $secondRun = app(TicketIssuanceService::class)->issueForOrder($order->public_id);

    expect($secondRun)->toBe(0)
        ->and(Ticket::query()->count())->toBe(2);
});

it('lets the DB judge block issuance on redis/db drift', function (): void {
    $order = heldOrder();   // qty=2، ظرفیت ۱۰
    $service = app(PaymentService::class);
    $payment = $service->initiate($order, 'https://evento.test/callback');

    // مهندسی drift: قبل از صدور، ۹ از ۱۰ فروخته «شده»
    DB::table('ticket_types')->update(['sold_cache' => 9]);

    try {
        $service->handleCallback($payment->gateway_token);
        $this->fail('Oversell judge did not fire.');
    } catch (OversellDetectedException) {
    }

    expect(Ticket::query()->count())->toBe(0)
        ->and(DB::table('ticket_types')->value('sold_cache'))->toBe(9);   // rollback کامل
});
