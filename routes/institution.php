<?php

use App\Http\Controllers\Application\BatchController;
use App\Http\Controllers\Application\InstitutionDashboardController;
use App\Http\Controllers\Application\InstitutionPaymentController;
use App\Http\Controllers\Application\InstitutionReportedIssuesController;
use App\Http\Controllers\Application\InstitutionSubscriptionController;
use App\Http\Controllers\Application\InstitutionSupportController;
use App\Http\Controllers\Application\InstitutionUserController;
use App\Http\Middleware\EnsureInstitutionAccess;
use App\Http\Middleware\EnsureSubscriptionActive;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Institution Routes (all data scoped to current institution)
|--------------------------------------------------------------------------
*/
Route::prefix('institution')->middleware(['auth', 'verified', EnsureInstitutionAccess::class])->group(function () {
    Route::get('complete-subscription', [InstitutionSubscriptionController::class, 'show'])->name('institution.complete-subscription');
    Route::post('complete-subscription/checkout', [InstitutionSubscriptionController::class, 'checkout'])->name('institution.complete-subscription.checkout');

    Route::middleware(EnsureSubscriptionActive::class)->group(function () {
        Route::get('dashboard', [InstitutionDashboardController::class, 'index'])->name('institution.dashboard');

        Route::get('payment', [InstitutionPaymentController::class, 'index'])->name('institution.payment');
        Route::post('payment/cancel', [InstitutionPaymentController::class, 'cancel'])->name('institution.payment.cancel');

        Route::get('batches', [BatchController::class, 'index'])->name('institution.batches');
        Route::post('batches', [BatchController::class, 'store'])->name('institution.batches.store');
        Route::delete('batches/{id}', [BatchController::class, 'destroy'])->name('institution.batches.destroy');

        Route::get('lecturers', [InstitutionUserController::class, 'lecturers'])->name('institution.lecturers');
        Route::get('lecturers/add', [InstitutionUserController::class, 'createLecturer'])->name('institution.lecturers.add');
        Route::post('lecturers', [InstitutionUserController::class, 'storeLecturer'])->name('institution.lecturers.store');
        Route::get('lecturers/{id}/edit', [InstitutionUserController::class, 'editLecturer'])->name('institution.lecturers.edit');
        Route::put('lecturers/{id}', [InstitutionUserController::class, 'updateLecturer'])->name('institution.lecturers.update');
        Route::delete('lecturers/{id}', [InstitutionUserController::class, 'destroyLecturer'])->name('institution.lecturers.destroy');

        Route::get('students', [InstitutionUserController::class, 'students'])->name('institution.students');
        Route::get('students/add', [InstitutionUserController::class, 'createStudent'])->name('institution.students.add');
        Route::post('students', [InstitutionUserController::class, 'storeStudent'])->name('institution.students.store');
        Route::get('students/{id}/edit', [InstitutionUserController::class, 'editStudent'])->name('institution.students.edit');
        Route::put('students/{id}', [InstitutionUserController::class, 'updateStudent'])->name('institution.students.update');
        Route::delete('students/{id}', [InstitutionUserController::class, 'destroyStudent'])->name('institution.students.destroy');

        Route::get('support', [InstitutionSupportController::class, 'index'])->name('institution.support');
        Route::get('support/create', [InstitutionSupportController::class, 'create'])->name('institution.support.create');
        Route::post('support', [InstitutionSupportController::class, 'store'])->name('institution.support.store');
        Route::get('support/{id}', [InstitutionSupportController::class, 'show'])->name('institution.support.show');

        Route::get('reported-issues', [InstitutionReportedIssuesController::class, 'index'])->name('institution.reported-issues');
        Route::get('reported-issues/{id}', [InstitutionReportedIssuesController::class, 'show'])->name('institution.reported-issues.show');
        Route::post('reported-issues/{id}/reviewed', [InstitutionReportedIssuesController::class, 'markReviewed'])->name('institution.reported-issues.reviewed');
        Route::post('reported-issues/{id}/escalate', [InstitutionReportedIssuesController::class, 'escalate'])->name('institution.reported-issues.escalate');
    });
});
