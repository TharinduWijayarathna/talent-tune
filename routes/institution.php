<?php

use App\Http\Controllers\Application\BatchController;
use App\Http\Controllers\Application\InstitutionUserController;
use App\Http\Middleware\EnsureInstitutionAccess;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Institution Routes (all data scoped to current institution)
|--------------------------------------------------------------------------
*/
Route::prefix('institution')->middleware(['auth', 'verified', EnsureInstitutionAccess::class])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('institution/Dashboard');
    })->name('institution.dashboard');

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
});
