<?php

declare(strict_types=1);

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Identity\Enums\OtpChannel;
use Modules\Identity\Enums\OtpPurpose;
use Modules\Identity\Http\Requests\RequestOtpRequest;
use Modules\Identity\Http\Requests\VerifyOtpRequest;
use Modules\Identity\Services\OtpService;
use Modules\Identity\Services\PhoneAuthService;

final class AuthController extends Controller
{


    public function requestOtp(RequestOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $otpService->issue($request->phone(), OtpChannel::Sms, OtpPurpose::Login);

        return response()->json([
            'message' => 'در صورت معتبر بودن شماره، کد تأیید ارسال شد.',
            'ttl' => (int) config('identity.otp.ttl_seconds'),
        ],202);
    }


    public function verifyOtp(VerifyOtpRequest $request, PhoneAuthService $authService):JsonResponse
    {
        $result=$authService->loginWithOtp(
            $request->phone(),
            $request->code(),
            $request->deviceName(),
            $request->ip(),
            $request->userAgent()
        );
        return response()->json([
            'token' => $result->token,
            'user_public_id' => $result->userPublicId,
            'is_new_user' => $result->isNewUser,
        ], $result->isNewUser ? 201 : 200);
    }

}
