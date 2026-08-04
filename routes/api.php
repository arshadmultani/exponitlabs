<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/sync')->group(function () {
    Route::get('/master-data', [SyncController::class, 'masterData']);
    Route::post('/doctors-batch', [SyncController::class, 'syncDoctors']);
    Route::post('/dcr-batch', [SyncController::class, 'syncDcrs']);
});
