<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.store');
});

Route::middleware(['auth', 'activity'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');

    Route::middleware('role:administrador,empleado')->group(function (): void {
        Route::get('/screenings', [ScreeningController::class, 'index'])->name('screenings.index');
        Route::get('/screenings/create', [ScreeningController::class, 'create'])->name('screenings.create');
        Route::post('/screenings', [ScreeningController::class, 'store'])->name('screenings.store');
        Route::get('/screenings/{screening}/edit', [ScreeningController::class, 'edit'])->name('screenings.edit');
        Route::put('/screenings/{screening}', [ScreeningController::class, 'update'])->name('screenings.update');
        Route::delete('/screenings/{screening}', [ScreeningController::class, 'destroy'])->name('screenings.destroy');
    });

    Route::middleware('role:administrador')->group(function (): void {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('movies', MovieController::class)->except('show');
    });

    Route::middleware('role:cliente')->group(function (): void {
        Route::get('/screenings/{screening}/seats', [ReservationController::class, 'show'])->name('reservations.seats');
        Route::post('/screenings/{screening}/reservations', [ReservationController::class, 'store'])->middleware('throttle:10,1')->name('reservations.store');
    });
});
