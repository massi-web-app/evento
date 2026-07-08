<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Identity\Enums\OtpChannel;
use Modules\Identity\Enums\OtpPurpose;
use Modules\Identity\Events\OtpRequested;
use Modules\Identity\Exceptions\InvalidOtpException;
use Modules\Identity\Exceptions\OtpRateLimitExceededException;
use Modules\Identity\Models\OtpCode;
use Modules\Identity\Services\OtpService;
use Modules\Settings\Database\Seeders\SettingDefinitionsSeeder;
use Modules\Settings\Services\SettingsService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(SettingDefinitionsSeeder::class);
    RateLimiter::clear('otp:send:09120000000');
});

it('issues a hashed otp and dispatches OtpRequested', function (): void {
    Event::fake([OtpRequested::class]);
    $service = app(OtpService::class);
    $otp = $service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);

    expect($otp->code_hash)->not->toHaveLength(6);   // hash است، نه کد خام

    Event::assertDispatched(OtpRequested::class, function (OtpRequested $e) use ($otp): bool {
        return $e->identifier === '09120000000'
            && strlen($e->plainCode) === 6
            && app('hash')->check($e->plainCode, $otp->code_hash);
    });
});

it('invalidates previous codes when issuing a new one', function (): void {
    Event::fake();
    $service = app(OtpService::class);
    $first = $service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);
    $service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);

    expect($first->refresh()->isConsumed())->toBeTrue();
});

it('blocks the 4th send within the window (anti sms-bombing)', function (): void {
    Event::fake();
    $service = app(OtpService::class);
    foreach (range(1, 3) as $i) {
        $service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);
    }

    $service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);
})->throws(OtpRateLimitExceededException::class);

it('verifies a correct code and consumes it', function (): void {
    $service = app(OtpService::class);
    $plain = interceptPlainCode($service);
    $service = app(OtpService::class);
    $otp = $service->verify('09120000000', OtpPurpose::Login, $plain);

    expect($otp->isConsumed())->toBeTrue();
});

it('rejects a wrong code and counts the attempt', function (): void {

    $service = app(OtpService::class);
    interceptPlainCode($service);

    try {
        $service->verify('09120000000', OtpPurpose::Login, '000000');
    } catch (InvalidOtpException) {
    }

    expect(OtpCode::query()->latest('id')->firstOrFail()->attempts)->toBe(1);
});

it('exhausts attempts after max wrong tries (anti brute-force)', function (): void {
    $service = app(OtpService::class);
    interceptPlainCode($service);

    foreach (range(1, 5) as $i) {

        try {
            $service->verify('09120000000', OtpPurpose::Login, '000000');
        } catch (InvalidOtpException) {
        }
    }

    $service->verify('09120000000', OtpPurpose::Login, '000000');
})->throws(InvalidOtpException::class, 'Maximum verification attempts');

it('rejects an expired code', function (): void {
    $service = app(OtpService::class);
    $plain = interceptPlainCode($service);

    /** @phpstan-ignore method.notFound (Pest binds $this to Laravel TestCase at runtime) */
    $this->travel(3)->minutes();   // ttl=120s → منقضی

    $service->verify('09120000000', OtpPurpose::Login, $plain);
})->throws(InvalidOtpException::class);

it('honors runtime-changed ttl from settings', function (): void {
    $this->seed(SettingDefinitionsSeeder::class);
    app(SettingsService::class)
        ->set('otp.expiry_seconds', 30);   // عملیات، مهلت را کم کرد

    $plain = interceptPlainCode(app(OtpService::class));

    /** @phpstan-ignore method.notFound (Pest binds $this to Laravel TestCase at runtime) */
    $this->travel(45)->seconds();          // ۴۵ ثانیه — بیشتر از ۳۰، کمتر از ۱۲۰ قدیمی

    app(OtpService::class)->verify('09120000000', OtpPurpose::Login, $plain);
})->throws(InvalidOtpException::class);

/** صدور کد و شکار کد خام از روی event — بدون دست زدن به internals */
/** صدور کد و شکار کد خام از روی event زنده — در این تست‌ها Event::fake نزن */
function interceptPlainCode(OtpService $service): string
{
    $plain = null;

    Event::listen(OtpRequested::class, function (OtpRequested $e) use (&$plain): void {
        $plain = $e->plainCode;
    });

    $service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);

    return $plain ?? throw new RuntimeException('OTP event not captured');
}
