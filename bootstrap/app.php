<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Identity\Exceptions\AccountNotAllowedException;
use Modules\Identity\Exceptions\InvalidOtpException;
use Modules\Identity\Exceptions\OtpRateLimitExceededException;

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
    })->create();
