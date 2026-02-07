<?php

namespace App\Services\Application;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class DashboardService
{
    public function getRedirectForCurrentUser(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return Redirect::route('login');
        }

        $route = $this->getDashboardRouteForRole($user->role ?? 'student');

        return Redirect::route($route);
    }

    public function getDashboardRouteForRole(string $role): string
    {
        if ($role === 'admin') {
            return 'admin.dashboard';
        }

        return match ($role) {
            'student' => 'student.dashboard',
            'lecturer' => 'lecturer.dashboard',
            'institution' => 'institution.dashboard',
            default => 'student.dashboard',
        };
    }
}
