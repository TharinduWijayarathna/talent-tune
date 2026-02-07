<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstitutionAccess
{
    /**
     * Handle an incoming request.
     *
     * Ensure that authenticated users can only access their institution's data.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $institution = $request->attributes->get('institution');

        // Admin users can access any route without institution context
        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        // Admin routes don't need institution context
        if ($request->routeIs('admin.*')) {
            return $next($request);
        }

        // If user is logged in but institution doesn't match, deny access
        if ($user && $institution) {
            // User must belong to the institution (unless admin)
            if ($user->institution_id !== $institution->id) {
                abort(403, 'You do not have access to this institution.');
            }
        }

        // If user is logged in but no institution context, redirect to home
        if ($user && ! $institution && $user->institution_id) {
            // Try to redirect to their institution
            $userInstitution = $user->institution;
            if ($userInstitution) {
                $baseDomain = config('domain.domain');
                if ($baseDomain === null || $baseDomain === '') {
                    $host = $request->getHost();
                    $parts = explode('.', $host);
                    $baseDomain = count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host;
                }
                $newHost = "{$userInstitution->slug}.{$baseDomain}";

                return redirect()->to("{$request->getScheme()}://{$newHost}{$request->getPathInfo()}");
            }

            return redirect()->route('home');
        }

        return $next($request);
    }
}
