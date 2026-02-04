<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetInstitutionContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $institution = $this->detectInstitution($request);

        if (!$institution) {
            // If no institution detected and not on public routes, redirect to institution selection
            if (!$this->isPublicRoute($request)) {
                return redirect()->route('home');
            }
        } else {
            // Set institution in request
            $request->merge(['institution' => $institution]);
            $request->attributes->set('institution', $institution);
            
            // Share with view
            view()->share('institution', $institution);
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
        
        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'app') {
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

        // Method 4: Check session (for logged-in users)
        if ($request->user() && $request->user()->institution_id) {
            $institution = Institution::find($request->user()->institution_id);
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
        $publicRoutes = [
            'home',
            'login',
            'register',
            'password.request',
            'password.reset',
            'register-institution',
            'register-institution.store',
            'register-institution.success',
        ];

        return $request->routeIs($publicRoutes);
    }
}
