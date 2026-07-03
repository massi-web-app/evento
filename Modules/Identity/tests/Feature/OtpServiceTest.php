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

uses(RefreshDatabase::class);

beforeEach(function (): void {
    RateLimiter::clear('otp:send:09120000000');
    $this->service = app(OtpService::class);
});

it('issues a hashed otp and dispatches OtpRequested', function (): void {
    Event::fake([OtpRequested::class]);

    $otp = $this->service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);

    expect($otp->code_hash)->not->toHaveLength(6);   // hash است، نه کد خام

    Event::assertDispatched(OtpRequested::class, function (OtpRequested $e) use ($otp): bool {
        return $e->identifier === '09120000000'
            && strlen($e->plainCode) === 6
            && app('hash')->check($e->plainCode, $otp->code_hash);
    });
});

it('invalidates previous codes when issuing a new one', function (): void {
    Event::fake();

    $first = $this->service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);
    $this->service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);

    expect($first->refresh()->isConsumed())->toBeTrue();
});

it('blocks the 4th send within the window (anti sms-bombing)', function (): void {
    Event::fake();

    foreach (range(1, 3) as $i) {
        $this->service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);
    }

    $this->service->issue('09120000000', OtpChannel::Sms, OtpPurpose::Login);
})->throws(OtpRateLimitExceededException::class);

it('verifies a correct code and consumes it', function (): void {
    $plain = interceptPlainCode($this->service);

    $otp = $this->service->verify('09120000000', OtpPurpose::Login, $plain);

    expect($otp->isConsumed())->toBeTrue();
});

it('rejects a wrong code and counts the attempt', function (): void {
    interceptPlainCode($this->service);

    try {
        $this->service->verify('09120000000', OtpPurpose::Login, '000000');
    } catch (InvalidOtpException) {
    }

    expect(OtpCode::query()->latest('id')->firstOrFail()->attempts)->toBe(1);
});

it('exhausts attempts after max wrong tries (anti brute-force)', function (): void {
    interceptPlainCode($this->service);

    foreach (range(1, 5) as $i) {
        try {
            $this->service->verify('09120000000', OtpPurpose::Login, '000000');
        } catch (InvalidOtpException) {
        }
    }

    $this->service->verify('09120000000', OtpPurpose::Login, '000000');
})->throws(InvalidOtpException::class, 'Maximum verification attempts');

it('rejects an expired code', function (): void {
    $plain = interceptPlainCode($this->service);

    $this->travel(3)->minutes();   // ttl=120s → منقضی

    $this->service->verify('09120000000', OtpPurpose::Login, $plain);
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
