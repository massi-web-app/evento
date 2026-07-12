<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Identity\Database\Seeders\RbacSeeder;
use Modules\Identity\Models\User;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Settings\Database\Seeders\SettingDefinitionsSeeder;
use Modules\Ticketing\Models\Ticket;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(SettingDefinitionsSeeder::class);
});

it('completes the full purchase over HTTP: hold → pay → callback → tickets', function (): void {
    $tt = makeOnSaleTicketType();
    $buyer = User::factory()->create();
    Sanctum::actingAs($buyer);

    // ۱) رزرو
    $holdResponse = $this->postJson(route('api.orders.hold'), [
        'ticket_type_id' => $tt->public_id,
        'quantity' => 2,
    ]);
    $holdResponse->assertStatus(201)
        ->assertJsonPath('data.status', OrderStatus::Pending->value)
        ->assertJsonPath('data.items.0.name', 'عادی');
    $orderId = $holdResponse->json('data.id');

    // ۲) درخواست پرداخت
    $payResponse = $this->postJson(route('api.orders.pay', ['order' => $orderId]));
    $payResponse->assertOk()->assertJsonStructure(['payment_id', 'redirect_url']);

    // ۳) برگشت از «بانک»
    $token = Payment::query()->firstOrFail()->gateway_token;
    $this->get(route('api.payments.callback', ['token' => $token]))
        ->assertRedirect()
        ->assertRedirectContains('status=success');

    expect(Order::query()->firstOrFail()->status)->toBe(OrderStatus::Paid)
        ->and(Ticket::query()->count())->toBe(2);
});

it('blocks paying someone else’s order', function (): void {
    $tt = makeOnSaleTicketType();
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);
    $orderId = $this->postJson(route('api.orders.hold'), [
        'ticket_type_id' => $tt->public_id,
        'quantity' => 1,
    ])->json('data.id');

    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.orders.pay', ['order' => $orderId]))
        ->assertStatus(403);
});

it('maps insufficient capacity to 409 over HTTP', function (): void {
    $tt = makeOnSaleTicketType(capacity: 1);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.orders.hold'), [
        'ticket_type_id' => $tt->public_id,
        'quantity' => 2,
    ])->assertStatus(422);   // خارج از ظرفیت ولی داخل بازهٔ min/max؟ نه — quantity=2 > capacity=1 → InsufficientCapacity → 409
})->skip('سناریو را پایین دقیق کردیم');

it('maps insufficient capacity to 409', function (): void {
    $tt = makeOnSaleTicketType(capacity: 3);
    Sanctum::actingAs(User::factory()->create());

    // اول ۳ صندلی را بگیر
    $this->postJson(route('api.orders.hold'), ['ticket_type_id' => $tt->public_id, 'quantity' => 3])
        ->assertStatus(201);

    // حالا هیچ‌چیز نمانده
    Sanctum::actingAs(User::factory()->create());
    $this->postJson(route('api.orders.hold'), ['ticket_type_id' => $tt->public_id, 'quantity' => 1])
        ->assertStatus(409);
});

it('redirects to failed result on gateway failure', function (): void {
    $tt = makeOnSaleTicketType();
    Sanctum::actingAs(User::factory()->create());
    $orderId = $this->postJson(route('api.orders.hold'), [
        'ticket_type_id' => $tt->public_id,
        'quantity' => 1,
    ])->json('data.id');
    $this->postJson(route('api.orders.pay', ['order' => $orderId]));

    $payment = Payment::query()->firstOrFail();
    $payment->forceFill(['gateway_token' => 'FAIL-' . $payment->gateway_token])->save();

    $this->get(route('api.payments.callback', ['token' => $payment->gateway_token]))
        ->assertRedirectContains('status=failed');
});
