<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Identity\Events\OtpRequested;
use Modules\Identity\Events\UserRegistered;
use Modules\Identity\Models\User;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    RateLimiter::clear('otp:send:09121112233');
});

it('completes the full login flow for a brand-new user', function (): void {
    Event::fake([UserRegistered::class]);   // فقط این — OtpRequested زنده می‌ماند برای شکار کد

    $plain = null;
    Event::listen(OtpRequested::class, function (OtpRequested $e) use (&$plain): void {
        $plain = $e->plainCode;
    });


    $this->postJson(route('api.auth.otp.request'), ['phone' => '09121112233'])
        ->assertStatus(202);

    $response = $this->postJson(route('api.auth.otp.verify'), [
        'phone' => '09121112233',
        'code' => $plain,
        'device_name' => 'Pixel 9',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['token', 'user_public_id', 'is_new_user'])
        ->assertJson(['is_new_user' => true]);

    expect(User::query()->where('phone', '09121112233')->exists())->toBeTrue();
    Event::assertDispatched(UserRegistered::class);

    // توکن صادرشده واقعاً کار می‌کند؟
    $this->withToken($response->json('token'))
        ->getJson('/api/user-check-placeholder');   // فعلاً route نداریم — این خط را بعد از اولین endpoint محافظت‌شده فعال می‌کنیم
})->skip(false);

it('logs in an existing user with 200 and no registration event', function (): void {
    User::factory()->create(['phone' => '09121112233']);
    Event::fake([UserRegistered::class]);

    $plain = null;
    Event::listen(OtpRequested::class, function (OtpRequested $e) use (&$plain): void {
        $plain = $e->plainCode;
    });

    $this->postJson(route('api.auth.otp.request'), ['phone' => '09121112233']);

    $this->postJson(route('api.auth.otp.verify'), [
        'phone' => '09121112233',
        'code' => $plain,
    ])->assertStatus(200)->assertJson(['is_new_user' => false]);

    Event::assertNotDispatched(UserRegistered::class);
});

it('rejects a malformed phone with 422 before touching the domain', function (): void {
    $this->postJson(route('api.auth.otp.request'), ['phone' => '12345'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('phone');
});

it('answers wrong codes with a generic 422', function (): void {
    $this->postJson(route('api.auth.otp.request'), ['phone' => '09121112233']);

    $this->postJson(route('api.auth.otp.verify'), [
        'phone' => '09121112233',
        'code' => '000000',
    ])->assertStatus(422);
});

it('returns 403 for a banned user with a valid code', function (): void {
    User::factory()->banned()->create(['phone' => '09121112233']);

    $plain = null;
    Event::listen(OtpRequested::class, function (OtpRequested $e) use (&$plain): void {
        $plain = $e->plainCode;
    });

    $this->postJson(route('api.auth.otp.request'), ['phone' => '09121112233']);

    $this->postJson(route('api.auth.otp.verify'), [
        'phone' => '09121112233',
        'code' => $plain,
    ])->assertStatus(403);
});
