<?php

use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RideController;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — RCCG Camp Chariot
|--------------------------------------------------------------------------
| All routes here are protected by Sanctum token auth.
| Tokens are issued on login and embedded in the Blade layout meta tag.
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // ── LOCATION (driver only) ─────────────────────────────────────────
    Route::post('/driver/location', [LocationController::class, 'update']);

    // ── DRIVER STATUS ──────────────────────────────────────────────────
    Route::post('/driver/toggle-availability', [DriverController::class, 'toggleAvailability']);

    // ── DRIVER: RIDE MANAGEMENT ────────────────────────────────────────
    Route::post('/driver/create-ride',                    [RideController::class, 'create']);
    Route::delete('/driver/cancel-ride',                  [RideController::class, 'cancel']);
    Route::post('/driver/requests/{rideRequest}/accept',  [RideController::class, 'acceptRequest']);
    Route::post('/driver/requests/{rideRequest}/decline', [RideController::class, 'declineRequest']);
    Route::post('/driver/rides/{ride}/complete',          [RideController::class, 'complete']);

    // ── RIDER: RIDE BROWSING & REQUESTING ─────────────────────────────
    Route::get('/rides/nearby',                           [RideController::class, 'nearby']);
    Route::post('/rides/{ride}/request',                  [RideController::class, 'requestRide'])
        ->middleware('ride.ratelimit');
    Route::delete('/rides/{rideRequest}/cancel-request',  [RideController::class, 'cancelRequest']);

    // ── NOTIFICATIONS ──────────────────────────────────────────────────
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // ── ZONES ──────────────────────────────────────────────────────────
    Route::get('/zones', fn() => Zone::active()->orderBy('name')->get());

    // ── AUTHENTICATED USER INFO ────────────────────────────────────────
    Route::get('/user', fn(Request $r) => $r->user()->load('driverProfile'));
});
