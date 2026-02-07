<?php

use App\Http\Controllers\Application\LecturerController;
use App\Http\Middleware\EnsureInstitutionAccess;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Lecturer Routes (all data scoped to current institution)
|--------------------------------------------------------------------------
*/
Route::prefix('lecturer')->middleware(['auth', 'verified', EnsureInstitutionAccess::class])->group(function () {
    Route::get('dashboard', [LecturerController::class, 'dashboard'])->name('lecturer.dashboard');
    Route::get('vivas', [LecturerController::class, 'vivas'])->name('lecturer.vivas');
    Route::get('vivas/create', [LecturerController::class, 'createViva'])->name('lecturer.vivas.create');
    Route::post('vivas', [LecturerController::class, 'storeViva'])->name('lecturer.vivas.store');
    Route::get('vivas/{id}', [LecturerController::class, 'showViva'])->name('lecturer.vivas.show');
});
