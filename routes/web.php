<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArController;

// Public augmented-reality viewer. Filament still owns "/".
// The QR code printed on a creative points here; only published creatives are visible.
Route::get('/ar/{creative}', [ArController::class, 'show'])->name('ar.show');

// Admin-only: receives the ".mind" tracking file compiled in the browser.
Route::middleware('auth')
    ->post('/admin/ar/{creative}/compile', [ArController::class, 'storeMind'])
    ->name('ar.compile');
