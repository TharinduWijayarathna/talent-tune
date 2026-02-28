<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Application\InstitutionController;
use App\Http\Middleware\EnsureAdminRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (no institution context required)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'verified', EnsureAdminRole::class])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('institutions', [InstitutionController::class, 'index'])->name('admin.institutions');
    Route::get('institutions/{institution}/edit', [InstitutionController::class, 'edit'])->name('admin.institutions.edit');
    Route::put('institutions/{institution}', [InstitutionController::class, 'update'])->name('admin.institutions.update');
    Route::patch('institutions/{institution}/status', [InstitutionController::class, 'updateStatus'])->name('admin.institutions.status');
    Route::delete('institutions/{institution}', [InstitutionController::class, 'destroy'])->name('admin.institutions.destroy');

    Route::get('users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::get('users/institution-admins', [AdminUserController::class, 'institutionAdmins'])->name('admin.users.institution-admins');
    Route::get('users/students', [AdminUserController::class, 'students'])->name('admin.users.students');
    Route::get('users/lecturers', [AdminUserController::class, 'lecturers'])->name('admin.users.lecturers');
    Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::get('payments', [AdminPaymentController::class, 'index'])->name('admin.payments');
    Route::get('payments/{id}', [AdminPaymentController::class, 'show'])->name('admin.payments.show');

    Route::get('support', [AdminSupportController::class, 'index'])->name('admin.support');
    Route::get('support/{id}', [AdminSupportController::class, 'show'])->name('admin.support.show');
    Route::post('support/{id}/reply', [AdminSupportController::class, 'reply'])->name('admin.support.reply');
    Route::patch('support/{id}/status', [AdminSupportController::class, 'updateStatus'])->name('admin.support.status');
});
