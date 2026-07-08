<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\AuthController;
use Modules\Identity\Http\Controllers\OrganizerController;

Route::prefix('auth/otp')->middleware('throttle:10,1')->group(function (): void {
    Route::post('request', [AuthController::class, 'requestOtp'])->name('auth.otp.request');
    Route::post('verify', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify');
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('organizers', [OrganizerController::class, 'store'])->name('organizers.store');
});
