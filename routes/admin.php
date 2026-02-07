<?php

use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\InstitutionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Admin Routes (no institution context required)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('institutions', [InstitutionController::class, 'index'])->name('admin.institutions');
    Route::patch('institutions/{institution}/status', [InstitutionController::class, 'updateStatus'])->name('admin.institutions.status');
    Route::delete('institutions/{institution}', [InstitutionController::class, 'destroy'])->name('admin.institutions.destroy');

    Route::get('users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::get('payments', [AdminPaymentController::class, 'index'])->name('admin.payments');
});
