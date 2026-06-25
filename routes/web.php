<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\DriverDashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RiderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — RCCG Camp Chariot
|--------------------------------------------------------------------------
*/

// ── PUBLIC ────────────────────────────────────────────────────────────────
Route::get('/', [PageController::class, 'welcome'])->name('home');

// ── AUTH (guest only) ─────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register',  [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login',     [LoginController::class, 'showForm'])->name('login');
    Route::post('/login',    [LoginController::class, 'authenticate']);
});

// Verification (auth required but not verified)
Route::middleware('auth')->group(function () {
    Route::get('/verify',       [VerificationController::class, 'showForm'])->name('verify');
    Route::post('/verify',      [VerificationController::class, 'verify'])->name('verify.submit');
    Route::post('/resend-otp',  [VerificationController::class, 'resend'])->name('resend-otp');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── RIDER ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified.member'])
    ->prefix('rider')
    ->name('rider.')
    ->group(function () {
        Route::get('/home',       [RiderController::class, 'home'])->name('home');
        Route::get('/find-ride',  [RiderController::class, 'findRide'])->name('find-ride');
        Route::get('/my-rides',   [RiderController::class, 'myRides'])->name('my-rides');
        Route::get('/profile',    [RiderController::class, 'profile'])->name('profile');
        Route::put('/profile',    [RiderController::class, 'updateProfile'])->name('profile.update');
    });

// ── DRIVER ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified.member', 'is.driver'])
    ->prefix('driver')
    ->name('driver.')
    ->group(function () {
        Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
        Route::get('/requests',  [DriverDashboardController::class, 'requests'])->name('requests');
        Route::get('/history',   [DriverDashboardController::class, 'history'])->name('history');
        Route::get('/profile',   [DriverDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile',   [DriverDashboardController::class, 'updateProfile'])->name('profile.update');
    });

// ── ADMIN ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'is.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard',             [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users',                 [AdminController::class, 'users'])->name('users');
        Route::get('/rides',                 [AdminController::class, 'rides'])->name('rides');
        Route::get('/zones',                 [AdminController::class, 'zones'])->name('zones');
        Route::post('/zones',                [AdminController::class, 'storeZone'])->name('zones.store');
        Route::put('/zones/{zone}',          [AdminController::class, 'updateZone'])->name('zones.update');
        Route::delete('/zones/{zone}',       [AdminController::class, 'deleteZone'])->name('zones.delete');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
        Route::patch('/users/{user}/verify', [AdminController::class, 'verifyUser'])->name('users.verify');
        Route::patch('/users/{user}/role',   [AdminController::class, 'changeRole'])->name('users.role');
        Route::delete('/rides/{ride}',       [AdminController::class, 'cancelRide'])->name('rides.cancel');
    });
