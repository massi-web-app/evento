<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\CheckoutController;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('orders/hold', [CheckoutController::class, 'hold'])->name('orders.hold');
    Route::post('orders/{order}/pay', [CheckoutController::class, 'pay'])->name('orders.pay');
});


Route::get('payments/callback', [CheckoutController::class, 'callback'])->name('payments.callback');
