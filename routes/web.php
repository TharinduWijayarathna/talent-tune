<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (by domain / role)
|--------------------------------------------------------------------------
| site.php       → Application (Home, Dashboard, Institution registration), Auth
| admin.php      → Admin (institutions, users, payments)
| institution.php→ Application (InstitutionUserController – lecturers/students)
| lecturer.php   → Application (LecturerController)
| student.php    → Application (StudentController)
| generation.php → Ai (Gemini, TTS)
| settings.php   → Settings (Profile, Password, TwoFactor)
*/
require __DIR__.'/site.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
require __DIR__.'/lecturer.php';
require __DIR__.'/institution.php';
require __DIR__.'/generation.php';
require __DIR__.'/settings.php';
