<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Custom Authentication Routes (override Fortify's default)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    $institution = request()->attributes->get('institution');
    
    // If institution detected, show institution-specific homepage
    if ($institution) {
        return Inertia::render('home/HomePage', [
            'canRegister' => Features::enabled(Features::registration()),
            'institution' => $institution,
        ]);
    }
    
    // Otherwise, show public SaaS landing page
    return Inertia::render('home/LandingPage', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Institution Registration Routes (Public)
Route::prefix('register-institution')->group(function () {
    Route::get('/', [App\Http\Controllers\InstitutionController::class, 'create'])->name('register-institution');
    Route::post('/', [App\Http\Controllers\InstitutionController::class, 'store'])->name('register-institution.store');
    Route::get('/success/{id}', [App\Http\Controllers\InstitutionController::class, 'success'])->name('register-institution.success');
});

Route::get('dashboard', function () {
    $user = auth()->user();
    
    if (!$user) {
        return redirect()->route('login');
    }
    
    $role = $user->role ?? 'student';

    // Admin users go directly to admin dashboard (no institution context needed)
    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    // Other users need institution context, so redirect based on role
    return match($role) {
        'student' => redirect()->route('student.dashboard'),
        'lecturer' => redirect()->route('lecturer.dashboard'),
        'institution' => redirect()->route('institution.dashboard'),
        default => redirect()->route('student.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Student Routes
Route::prefix('student')->middleware(['auth', 'verified', \App\Http\Middleware\EnsureInstitutionAccess::class])->group(function () {
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
Route::prefix('lecturer')->middleware(['auth', 'verified', \App\Http\Middleware\EnsureInstitutionAccess::class])->group(function () {
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
Route::prefix('institution')->middleware(['auth', 'verified', \App\Http\Middleware\EnsureInstitutionAccess::class])->group(function () {
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

// Admin Routes (no institution context required)
Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');
    
    // Institution Management
    Route::get('institutions', [App\Http\Controllers\InstitutionController::class, 'index'])->name('admin.institutions');
    Route::patch('institutions/{institution}/status', [App\Http\Controllers\InstitutionController::class, 'updateStatus'])->name('admin.institutions.status');
    Route::delete('institutions/{institution}', [App\Http\Controllers\InstitutionController::class, 'destroy'])->name('admin.institutions.destroy');
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
    
    Route::post('conversation/response', [App\Http\Controllers\Viva\GeminiController::class, 'generateConversationalResponse'])
        ->name('viva.conversation.response');
});

require __DIR__.'/settings.php';
