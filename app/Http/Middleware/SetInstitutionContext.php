<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetInstitutionContext
{
    /**
     * Routes that should skip institution context checks entirely.
     */
    protected array $skipRoutes = [
        'login',
        'logout',
        'register',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'two-factor.login',
        'two-factor.challenge',
    ];

    /**
     * Path prefixes that should skip institution context checks.
     */
    protected array $skipPathPrefixes = [
        'admin/',
        'login',
        'logout',
        'register',
        'forgot-password',
        'reset-password',
        'email/verify',
        'two-factor-challenge',
        'register-institution',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $isLoginRoute = $path === 'login' || str_starts_with($path, 'login');

        // Detect institution even for login routes (needed for role selection)
        $institution = $this->detectInstitution($request);

        // Always set institution if detected (even for login routes)
        if ($institution) {
            $request->merge(['institution' => $institution]);
            $request->attributes->set('institution', $institution);
            view()->share('institution', $institution);
        }

        // Skip enforcement for authentication-related paths (but still detect institution above)
        foreach ($this->skipPathPrefixes as $prefix) {
            if (str_starts_with($path, $prefix) || $path === $prefix || $path === rtrim($prefix, '/')) {
                return $next($request);
            }
        }

        // Skip for admin routes by route name
        if ($request->routeIs('admin.*')) {
            return $next($request);
        }

        // Skip for routes that don't need institution context
        if ($request->routeIs($this->skipRoutes)) {
            return $next($request);
        }

        // Skip institution context for admin users on any route
        $user = $request->user();
        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        // If no institution detected and not on public routes, redirect to home
        if (! $institution && ! $this->isPublicRoute($request)) {
            return redirect()->route('home');
        }

        return $next($request);
    }

    /**
     * Detect institution from request.
     */
    protected function detectInstitution(Request $request): ?Institution
    {
        // Method 1: Check subdomain (e.g., university-tech.talenttune.com)
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'app' && $subdomain !== 'talenttune') {
            $institution = Institution::where('slug', $subdomain)
                ->active()
                ->first();

            if ($institution) {
                return $institution;
            }
        }

        // Method 2: Check custom domain
        $institution = Institution::where('domain', $host)
            ->active()
            ->first();

        if ($institution) {
            return $institution;
        }

        // Method 3: Check path parameter (e.g., /institution/university-tech/...)
        $pathInstitution = $request->route('institution');
        if ($pathInstitution) {
            $institution = Institution::where('slug', $pathInstitution)
                ->active()
                ->first();

            if ($institution) {
                return $institution;
            }
        }

        // Method 4: Check for logged-in user's institution
        $user = $request->user();
        if ($user && $user->institution_id) {
            $institution = Institution::find($user->institution_id);
            if ($institution && $institution->is_active) {
                return $institution;
            }
        }

        return null;
    }

    /**
     * Extract subdomain from host.
     */
    protected function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        // If we have more than 2 parts, the first part is likely the subdomain
        // e.g., university-tech.talenttune.com -> university-tech
        if (count($parts) >= 3) {
            return $parts[0];
        }

        // For local development (e.g., university-tech.test)
        if (count($parts) === 2 && str_ends_with($host, '.test')) {
            return $parts[0];
        }

        return null;
    }

    /**
     * Check if route is public (doesn't require institution context).
     */
    protected function isPublicRoute(Request $request): bool
    {
        $path = $request->path();

        // Check path prefixes for public routes
        foreach ($this->skipPathPrefixes as $prefix) {
            if (str_starts_with($path, $prefix) || $path === rtrim($prefix, '/')) {
                return true;
            }
        }

        // Root path is public
        if ($path === '/' || $path === '') {
            return true;
        }

        $publicRoutes = [
            'home',
            'login',
            'register',
            'password.request',
            'password.reset',
            'register-institution',
            'register-institution.store',
            'register-institution.success',
            'admin.*',
        ];

        return $request->routeIs($publicRoutes);
    }
}
