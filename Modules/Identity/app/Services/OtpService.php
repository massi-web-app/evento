<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Identity\Enums\OtpChannel;
use Modules\Identity\Enums\OtpPurpose;
use Modules\Identity\Events\OtpRequested;
use Modules\Identity\Exceptions\InvalidOtpException;
use Modules\Identity\Exceptions\OtpRateLimitExceededException;
use Modules\Identity\Models\OtpCode;
use Modules\Settings\Contracts\SettingsReader;

final readonly class OtpService
{
    public function __construct(
        private Hasher         $hasher,
        private SettingsReader $settings,
    )
    {
    }

    private const int CODE_LENGTH = 6;
    /**
     * صدور کد جدید برای یک شناسه (موبایل/ایمیل).
     *
     * @throws OtpRateLimitExceededException
     */
    public function issue(string $identifier, OtpChannel $channel, OtpPurpose $purpose): OtpCode
    {
        $this->guardSendRate($identifier);

        // کدهای زندهٔ قبلی همین شناسه/هدف باطل می‌شوند — همیشه فقط یک کد معتبر
        OtpCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $plainCode = $this->generateCode();

        $otp = OtpCode::query()->create([
            'identifier' => $identifier,
            'channel' => $channel,
            'purpose' => $purpose,
            'code_hash' => $this->hasher->make($plainCode),
            'max_attempts' => (int)$this->settings->get('otp.max_attempts'),
            'expires_at' => now()->addSeconds((int)$this->settings->get('otp.expiry_seconds')),
        ]);

        event(new OtpRequested(
            identifier: $identifier,
            channel: $channel,
            purpose: $purpose,
            plainCode: $plainCode,
            ttlSeconds: (int)$this->settings->get('otp.expiry_seconds'),
        ));

        return $otp;
    }

    /**
     * راستی‌آزمایی کد. موفقیت = مصرف کد.
     *
     * @throws InvalidOtpException
     */
    public function verify(string $identifier, OtpPurpose $purpose, string $plainCode): OtpCode
    {
        /** @var OtpCode|null $otp */
        $otp = OtpCode::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($otp === null) {
            throw InvalidOtpException::expiredOrMissing();
        }

        if (!$otp->hasAttemptsLeft()) {
            throw InvalidOtpException::attemptsExhausted();
        }

        $otp->increment('attempts');

        if (!$this->hasher->check($plainCode, $otp->code_hash)) {
            throw InvalidOtpException::wrongCode();
        }

        $otp->forceFill(['consumed_at' => now()])->save();

        RateLimiter::clear($this->sendKey($identifier));

        return $otp->refresh();
    }

    private function guardSendRate(string $identifier): void
    {
        $key = $this->sendKey($identifier);
        $max = (int)$this->settings->get('otp.send_max_per_window');

        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw OtpRateLimitExceededException::forSeconds(RateLimiter::availableIn($key));
        }

        RateLimiter::hit($key, (int)$this->settings->get('otp.send_window_seconds'));
    }

    private function generateCode(): string
    {
        $length = (int)$this->settings->get('otp.send_max_per_window');
        $max = (10 ** self::CODE_LENGTH) - 1;

        return str_pad((string) random_int(0, $max), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    private function sendKey(string $identifier): string
    {
        return "otp:send:{$identifier}";
    }
}
