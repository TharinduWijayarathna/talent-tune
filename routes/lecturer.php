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
    Route::post('vivas/{id}/close', [LecturerController::class, 'closeViva'])->name('lecturer.vivas.close');
    Route::get('vivas/{id}/students-for-late-participation', [LecturerController::class, 'studentsForLateParticipation'])->name('lecturer.vivas.students-for-late-participation');
    Route::post('vivas/{id}/add-late-student', [LecturerController::class, 'addLateStudent'])->name('lecturer.vivas.add-late-student');
    Route::get('viva-submissions/{submissionId}/document', [LecturerController::class, 'streamSubmissionDocument'])->name('lecturer.viva-submissions.document');
    Route::get('viva-submissions/{submissionId}/voice/{index}', [LecturerController::class, 'streamSubmissionVoice'])->name('lecturer.viva-submissions.voice');
});
