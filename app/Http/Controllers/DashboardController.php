<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect the user to the appropriate dashboard based on their role.
     */
    public function redirect(): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role ?? 'student';

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return match ($role) {
            'student' => redirect()->route('student.dashboard'),
            'lecturer' => redirect()->route('lecturer.dashboard'),
            'institution' => redirect()->route('institution.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
