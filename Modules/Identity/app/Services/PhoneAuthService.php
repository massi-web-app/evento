<?php

declare(strict_types=1);

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\DB;
use Modules\Identity\DTOs\AuthResult;
use Modules\Identity\Enums\OtpPurpose;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Events\UserRegistered;
use Modules\Identity\Exceptions\AccountNotAllowedException;
use Modules\Identity\Exceptions\InvalidOtpException;
use Modules\Identity\Models\User;

final readonly class PhoneAuthService
{
    public function __construct(
        private OtpService $otpService,
    ) {}

    /**
     * ورود/ثبت‌نام با موبایل: verify کد → find-or-create → صدور توکن.
     *
     * @throws InvalidOtpException
     * @throws AccountNotAllowedException
     */
    public function loginWithOtp(
        string $phone,
        string $code,
        ?string $deviceName = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): AuthResult {
        $this->otpService->verify($phone, OtpPurpose::Login, $code);

        [$user, $isNew] = DB::transaction(function () use ($phone): array {
            /** @var User|null $user */
            $user = User::query()->where('phone', $phone)->lockForUpdate()->first();

            if ($user === null) {
                $user = User::query()->create([
                    'phone' => $phone,
                    'locale' => 'fa',
                    'timezone' => 'Asia/Tehran',
                ]);

                $user->forceFill([
                    'status' => UserStatus::Active,
                    'phone_verified_at' => now(),
                ])->save();

                return [$user, true];
            }

            if (! $user->status->canAuthenticate()) {
                throw AccountNotAllowedException::forStatus($user->status);
            }

            return [$user, false];
        });

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress !== null ? inet_pton($ipAddress) : null,
        ])->save();

        $token = $user->createToken($deviceName ?? 'unknown-device');

        $token->accessToken->forceFill([
            'ip_address' => $ipAddress !== null ? inet_pton($ipAddress) : null,
            'user_agent' => $userAgent,
        ])->save();

        if ($isNew) {
            event(new UserRegistered(
                userPublicId: $user->public_id,
                phone: $phone,
            ));
        }

        return new AuthResult(
            token: $token->plainTextToken,
            userPublicId: $user->public_id,
            isNewUser: $isNew,
        );
    }
}
