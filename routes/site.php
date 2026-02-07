<?php

use App\Http\Controllers\Application\DashboardController;
use App\Http\Controllers\Application\HomeController;
use App\Http\Controllers\Application\InstitutionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site routes (auth, home, institution registration, dashboard redirect)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('register-institution')->group(function () {
    Route::get('/', [InstitutionController::class, 'create'])->name('register-institution');
    Route::post('/', [InstitutionController::class, 'store'])->name('register-institution.store');
    Route::get('/success/{id}', [InstitutionController::class, 'success'])->name('register-institution.success');
});

Route::get('dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
