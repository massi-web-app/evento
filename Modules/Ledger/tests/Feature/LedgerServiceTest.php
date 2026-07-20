<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Ledger\DTOs\EntryLine;
use Modules\Ledger\Exceptions\UnbalancedTransactionException;
use Modules\Ledger\Models\LedgerTransaction;
use Modules\Ledger\Services\AccountResolver;
use Modules\Ledger\Services\LedgerService;
use Modules\Orders\Models\Payment;
use Modules\Orders\Services\PaymentService;
use Modules\Settings\Database\Seeders\SettingDefinitionsSeeder;
use Modules\Shared\ValueObjects\Money;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(SettingDefinitionsSeeder::class);
});

it('refuses unbalanced transactions before touching the database', function (): void {
    $accounts = app(AccountResolver::class);
    $accounts->platformCash();

    try {
        app(LedgerService::class)->record(
            sourceType: 'test',
            sourceId: 'x1',
            description: 'ناتراز',
            occurredAt: CarbonImmutable::now(),
            lines: [
                EntryLine::debit('platform:cash', Money::irr(1_000)),
                EntryLine::credit('platform:commission_revenue', Money::irr(900)),
            ],
        );
        $this->fail('Unbalanced transaction was accepted.');
    } catch (UnbalancedTransactionException) {
    }

    expect(LedgerTransaction::query()->count())->toBe(0);
});

it('records a payment into the ledger end-to-end and stays balanced', function (): void {
    $order = heldOrder();   // qty=2 → total 1,000,000
    $service = app(PaymentService::class);
    $payment = $service->initiate($order, 'https://evento.test/callback');
    $service->handleCallback($payment->gateway_token);   // sync queue → listener

    $ledger = app(LedgerService::class);

    expect(LedgerTransaction::query()->count())->toBe(1)
        ->and($ledger->balanceOf('platform:cash'))->toBe(1_000_000)
        ->and($ledger->trialBalance())->toBe(0);
});

it('stays idempotent when the queue delivers the payment twice', function (): void {
    $order = heldOrder();
    $service = app(PaymentService::class);
    $payment = $service->initiate($order, 'https://evento.test/callback');
    $service->handleCallback($payment->gateway_token);

    // تحویل دوم — مستقیم خود listener
    $event = new \Modules\Orders\Events\PaymentVerified(
        paymentPublicId: Payment::query()->firstOrFail()->public_id,
        orderPublicId: $order->public_id,
        amount: 1_000_000,
        gatewayRef: 'RETRY',
    );
    app(\Modules\Ledger\Listeners\RecordPaymentInLedger::class)->handle($event);

    expect(LedgerTransaction::query()->count())->toBe(1)
        ->and(app(LedgerService::class)->trialBalance())->toBe(0);
});

it('keeps the whole book balanced across many purchases', function (): void {
    foreach (range(1, 5) as $i) {
        $order = heldOrder();
        $service = app(PaymentService::class);
        $payment = $service->initiate($order, 'https://evento.test/callback');
        $service->handleCallback($payment->gateway_token);
    }

    expect(LedgerTransaction::query()->count())->toBe(5)
        ->and(app(LedgerService::class)->trialBalance())->toBe(0);
});
