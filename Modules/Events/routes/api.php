<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\OrganizerEventController;
use Modules\Events\Http\Controllers\PublicEventController;

Route::middleware('auth:sanctum')->prefix('organizer')->group(function (): void {
    Route::post('events', [OrganizerEventController::class, 'store'])->name('organizer.events.store');
    Route::post('events/{event}/submit', [OrganizerEventController::class, 'submitForReview'])->name('organizer.events.submit');
});

Route::get('events', [PublicEventController::class, 'index'])->name('public.events.index');
