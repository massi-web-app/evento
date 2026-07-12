<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Models\User;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Enums\PaymentStatus;
use Modules\Orders\Events\PaymentVerified;
use Modules\Orders\Exceptions\PaymentNotPayableException;
use Modules\Orders\Exceptions\PaymentVerificationFailedException;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Orders\Services\HoldService;
use Modules\Orders\Services\PaymentService;
use Modules\Settings\Database\Seeders\SettingDefinitionsSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(SettingDefinitionsSeeder::class);
});

function heldOrder(): Order
{
    $tt = makeOnSaleTicketType();

    return app(HoldService::class)->hold(User::factory()->create()->id, $tt, 2);
}

it('completes the happy path: initiate → callback → paid', function (): void {
    EventFacade::fake([PaymentVerified::class]);
    $order = heldOrder();
    $service = app(PaymentService::class);

    $payment = $service->initiate($order, 'https://evento.test/callback');

    expect($payment->status)->toBe(PaymentStatus::Redirected)
        ->and($payment->gateway_token)->toStartWith('FAKE-')
        ->and($order->refresh()->status)->toBe(OrderStatus::AwaitingPayment);

    $verified = $service->handleCallback($payment->gateway_token);

    expect($verified->status)->toBe(PaymentStatus::Verified)
        ->and($verified->gateway_ref)->toStartWith('REF-')
        ->and($order->refresh()->status)->toBe(OrderStatus::Paid)
        ->and($order->paid_at)->not->toBeNull();

    EventFacade::assertDispatchedTimes(PaymentVerified::class, 1);
});

it('is idempotent on duplicate callbacks', function (): void {
    EventFacade::fake([PaymentVerified::class]);
    $order = heldOrder();
    $service = app(PaymentService::class);
    $payment = $service->initiate($order, 'https://evento.test/callback');

    $service->handleCallback($payment->gateway_token);
    $again = $service->handleCallback($payment->gateway_token);   // درگاه دوباره زد

    expect($again->status)->toBe(PaymentStatus::Verified);
    EventFacade::assertDispatchedTimes(PaymentVerified::class, 1);   // فقط یک‌بار
});

it('marks payment failed and keeps order unpaid on gateway failure', function (): void {
    $order = heldOrder();
    $service = app(PaymentService::class);
    $payment = $service->initiate($order, 'https://evento.test/callback');

    // سوییچ شکست FakeGateway
    $payment->forceFill(['gateway_token' => 'FAIL-' . $payment->gateway_token])->save();

    try {
        $service->handleCallback($payment->gateway_token);
    } catch (PaymentVerificationFailedException) {
    }

    expect($payment->refresh()->status)->toBe(PaymentStatus::Failed)
        ->and($order->refresh()->status)->toBe(OrderStatus::AwaitingPayment);   // hold هنوز زنده — تلاش دوباره مجاز
});

it('refuses to initiate payment for an expired hold', function (): void {
    $order = heldOrder();

    /** @phpstan-ignore method.notFound (Pest binds $this to Laravel TestCase at runtime) */
    $this->travel(20)->minutes();

    app(PaymentService::class)->initiate($order->refresh(), 'https://evento.test/callback');
})->throws(PaymentNotPayableException::class);
