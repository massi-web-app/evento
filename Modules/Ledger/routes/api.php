<?php

use Illuminate\Support\Facades\Route;
use Modules\Ledger\Http\Controllers\LedgerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('ledgers', LedgerController::class)->names('ledger');
});
