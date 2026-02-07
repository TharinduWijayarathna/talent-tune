<?php

use App\Http\Controllers\Application\StudentController;
use App\Http\Middleware\EnsureInstitutionAccess;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Routes (all data scoped to current institution)
|--------------------------------------------------------------------------
*/
Route::prefix('student')->middleware(['auth', 'verified', EnsureInstitutionAccess::class])->group(function () {
    Route::get('dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('vivas', [StudentController::class, 'vivas'])->name('student.vivas');
    Route::get('vivas/{id}/attend', [StudentController::class, 'attendViva'])->name('student.vivas.attend');
    Route::post('vivas/{id}/upload-document', [StudentController::class, 'uploadVivaDocument'])->name('student.vivas.upload-document');
    Route::post('vivas/complete-submission', [StudentController::class, 'completeVivaSubmission'])->name('student.vivas.complete-submission');
});
