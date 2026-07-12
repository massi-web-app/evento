<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Events\Exceptions\IllegalEventTransitionException;
use Modules\Events\Exceptions\InvalidEventScheduleException;
use Modules\Events\Exceptions\OrganizerNotActiveException;
use Modules\Events\Exceptions\VenueRequiredException;
use Modules\Identity\Exceptions\AccountNotAllowedException;
use Modules\Identity\Exceptions\InvalidOtpException;
use Modules\Identity\Exceptions\OrganizerAlreadyExistsException;
use Modules\Identity\Exceptions\OtpRateLimitExceededException;
use Modules\Orders\Exceptions\IllegalOrderTransitionException;
use Modules\Orders\Exceptions\InsufficientCapacityException;
use Modules\Orders\Exceptions\InvalidQuantityException;
use Modules\Settings\Exceptions\SettingNotDefinedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidOtpException $e) {
            return response()->json(['message' => 'کد واردشده معتبر نیست یا منقضی شده است.'], 422);
        });
        $exceptions->render(function (OtpRateLimitExceededException $e) {
            return response()->json([
                'message' => 'تعداد درخواست‌ها بیش از حد مجاز است.',
                'retry_after' => $e->retryAfter,
            ], 429)->header('Retry-After', (string) $e->retryAfter);
        });

        $exceptions->render(function (AccountNotAllowedException $e) {
            return response()->json(['message' => 'حساب شما امکان ورود ندارد.'], 403);
        });

        $exceptions->render(function (OrganizerAlreadyExistsException $e) {
            return response()->json(['message' => 'شما قبلاً یک پروفایل سازنده دارید.'], 409);
        });

        $exceptions->render(function (SettingNotDefinedException $e) {
            return response()->json(['message' => 'شما قبلاً یک پروفایل سازنده دارید.'], 409);
        });


        $exceptions->render(function (VenueRequiredException $e) {
            return response()->json(['message' => 'برای رویداد حضوری انتخاب محل برگزاری الزامی است.'], 422);
        });

        $exceptions->render(function (InvalidEventScheduleException $e) {
            return response()->json(['message' => 'زمان پایان رویداد باید بعد از زمان شروع باشد.'], 422);
        });

        $exceptions->render(function (OrganizerNotActiveException $e) {
            return response()->json(['message' => 'پروفایل سازندهٔ شما فعال نیست.'], 403);
        });

        $exceptions->render(function (IllegalEventTransitionException $e) {
            return response()->json(['message' => 'این تغییر وضعیت برای رویداد مجاز نیست.'], 409);
        });

        $exceptions->render(function (IllegalOrderTransitionException $e) {
            return response()->json(['message' => 'این تغییر وضعیت برای سفارش مجاز نیست.'], 409);
        });

        $exceptions->render(function (InsufficientCapacityException $e) {
            return response()->json(['message' => 'ظرفیت کافی برای این تعداد بلیت موجود نیست.'], 409);
        });

        $exceptions->render(function (InvalidQuantityException $e) {
            return response()->json(['message' => 'تعداد درخواستی خارج از محدودهٔ مجاز است.'], 422);
        });
    })->create();
