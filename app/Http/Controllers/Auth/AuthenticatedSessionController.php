<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request)
    {
        // Check if we're on an institution subdomain
        // First try to get from request attributes (set by middleware)
        $institution = $request->attributes->get('institution');
        
        // If not found, try to detect it directly (fallback)
        if (!$institution) {
            $institution = $this->detectInstitution($request);
        }
        
        $showRoleSelection = $institution !== null;

        return Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'status' => $request->session()->get('status'),
            'showRoleSelection' => $showRoleSelection,
            'institution' => $institution ? [
                'name' => $institution->name,
                'slug' => $institution->slug,
            ] : null,
        ]);
    }
    
    /**
     * Detect institution from request (helper method).
     */
    protected function detectInstitution(Request $request)
    {
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);
        
        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'app' && $subdomain !== 'talenttune') {
            $institution = \App\Models\Institution::where('slug', $subdomain)
                ->active()
                ->first();
            
            if ($institution) {
                return $institution;
            }
        }
        
        return null;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
                'role' => ['nullable', 'string', 'in:student,lecturer,institution'],
            ]);

            // Attempt authentication
            if (!Auth::attempt($credentials, $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'email' => __('These credentials do not match our records.'),
                ]);
            }

            $request->session()->regenerate();

            $user = Auth::user();

            // If role was selected and user is on institution subdomain, validate role matches
            $selectedRole = $request->input('role');
            $institution = $request->attributes->get('institution');
            
            if ($selectedRole && $institution && $user->role !== $selectedRole) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                throw ValidationException::withMessages([
                    'email' => "This account is registered as a {$user->role}, but you selected {$selectedRole}. Please select the correct role or contact support.",
                ]);
            }

            // Clear any intended URL to ensure clean redirect
            $request->session()->forget('url.intended');

            // Determine redirect based on user role
            $redirectUrl = $this->getRedirectUrl($user, $request);

            if ($request->wantsJson()) {
                return response()->json([
                    'two_factor' => false,
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect()->intended($redirectUrl);
        } catch (ValidationException $e) {
            // Preserve institution context on validation errors
            $institution = $request->attributes->get('institution');
            if (!$institution) {
                $institution = $this->detectInstitution($request);
            }
            
            // Re-throw the exception but ensure Inertia preserves the page state
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['redirect' => '/']);
        }

        return redirect('/');
    }

    /**
     * Get the redirect URL based on user role.
     */
    protected function getRedirectUrl($user, Request $request): string
    {
        // Admin users go directly to admin dashboard
        if ($user->role === 'admin') {
            return '/admin/dashboard';
        }

        // For other users, check if they have an institution
        $institution = $user->institution;

        if (!$institution || !$institution->is_active) {
            // No active institution, redirect to home with error
            return '/';
        }

        // Build subdomain URL if needed
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        // If we're already on the correct subdomain, redirect to role-specific dashboard
        if ($subdomain === $institution->slug) {
            return $this->getRoleDashboard($user->role);
        }

        // If we're on the main domain or wrong subdomain, redirect to institution subdomain
        $baseDomain = $this->getBaseDomain($host);
        $scheme = $request->getScheme();

        return "{$scheme}://{$institution->slug}.{$baseDomain}" . $this->getRoleDashboard($user->role);
    }

    /**
     * Get the dashboard URL for a given role.
     */
    protected function getRoleDashboard(string $role): string
    {
        return match ($role) {
            'student' => '/student/dashboard',
            'lecturer' => '/lecturer/dashboard',
            'institution' => '/institution/dashboard',
            default => '/student/dashboard',
        };
    }

    /**
     * Extract subdomain from host.
     */
    protected function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        // For local development (e.g., university-tech.talenttune.test)
        if (count($parts) >= 3) {
            return $parts[0];
        }

        // For .test domains (e.g., university-tech.test) - local dev
        if (count($parts) === 2 && str_ends_with($host, '.test')) {
            return $parts[0];
        }

        return null;
    }

    /**
     * Get the base domain from host.
     */
    protected function getBaseDomain(string $host): string
    {
        $parts = explode('.', $host);

        // For .test domains in local dev
        if (str_ends_with($host, '.test')) {
            // If just "talenttune.test", return as-is
            if (count($parts) === 2) {
                return $host;
            }
            // If "something.talenttune.test", return "talenttune.test"
            return implode('.', array_slice($parts, -2));
        }

        // For production domains like "talenttune.com" or "sub.talenttune.com"
        if (count($parts) >= 2) {
            return implode('.', array_slice($parts, -2));
        }

        return $host;
    }
}
