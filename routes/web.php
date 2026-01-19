<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('home/HomePage', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();
    $role = $user->role ?? 'student';

    // Redirect based on role
    return match($role) {
        'student' => redirect()->route('student.dashboard'),
        'lecturer' => redirect()->route('lecturer.dashboard'),
        'institution' => redirect()->route('institution.dashboard'),
        'admin' => redirect()->route('admin.dashboard'),
        default => redirect()->route('student.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Student Routes
Route::prefix('student')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('student/Dashboard');
    })->name('student.dashboard');

    Route::get('vivas', function () {
        return Inertia::render('student/VivaSessions');
    })->name('student.vivas');

    Route::get('vivas/{id}/attend', function ($id) {
        return Inertia::render('student/VivaAttend', ['vivaId' => $id]);
    })->name('student.vivas.attend');

    Route::get('marks', function () {
        return Inertia::render('student/Marks');
    })->name('student.marks');
});

// Lecturer Routes
Route::prefix('lecturer')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('lecturer/Dashboard');
    })->name('lecturer.dashboard');

    Route::get('vivas', function () {
        return Inertia::render('lecturer/Dashboard'); // You can create a list page later
    })->name('lecturer.vivas');

    Route::get('vivas/create', function () {
        return Inertia::render('lecturer/CreateViva');
    })->name('lecturer.vivas.create');

    Route::get('vivas/{id}', function ($id) {
        return Inertia::render('lecturer/Dashboard'); // You can create a detail page later
    })->name('lecturer.vivas.show');
});

// Institution Routes
Route::prefix('institution')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('institution/Dashboard');
    })->name('institution.dashboard');

    Route::get('lecturers', function () {
        return Inertia::render('institution/Lecturers');
    })->name('institution.lecturers');

    Route::get('lecturers/add', function () {
        return Inertia::render('institution/AddLecturer');
    })->name('institution.lecturers.add');

    Route::get('lecturers/{id}/edit', function ($id) {
        return Inertia::render('institution/EditLecturer', ['lecturerId' => $id]);
    })->name('institution.lecturers.edit');

    Route::get('students', function () {
        return Inertia::render('institution/Students');
    })->name('institution.students');

    Route::get('students/add', function () {
        return Inertia::render('institution/AddStudent');
    })->name('institution.students.add');

    Route::get('students/{id}/edit', function ($id) {
        return Inertia::render('institution/EditStudent', ['studentId' => $id]);
    })->name('institution.students.edit');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');
});

// Viva TTS API Route
Route::post('api/viva/tts', [App\Http\Controllers\Viva\TTSController::class, 'generate'])
    ->middleware(['auth'])
    ->name('viva.tts');

// Viva Gemini AI API Routes
Route::prefix('api/viva')->middleware(['auth'])->group(function () {
    Route::post('questions/generate', [App\Http\Controllers\Viva\GeminiController::class, 'generateQuestions'])
        ->name('viva.questions.generate');
    
    Route::post('answer/evaluate', [App\Http\Controllers\Viva\GeminiController::class, 'evaluateAnswer'])
        ->name('viva.answer.evaluate');
});

require __DIR__.'/settings.php';
