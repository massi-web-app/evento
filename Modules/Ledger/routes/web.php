<?php

use Illuminate\Support\Facades\Route;
use Modules\Ledger\Http\Controllers\LedgerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ledgers', LedgerController::class)->names('ledger');
});
