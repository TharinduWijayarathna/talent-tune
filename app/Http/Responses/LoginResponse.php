<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect('/dashboard');
        }
        
        // For admin users, redirect directly (no institution context needed)
        if ($user->role === 'admin') {
            $redirectPath = '/admin/dashboard';
            return $request->wantsJson()
                ? response()->json(['two_factor' => false, 'redirect' => $redirectPath])
                : redirect()->intended($redirectPath);
        }
        
        // For other users, check if they have an institution
        $institution = $user->institution;
        
        if ($institution && $institution->is_active) {
            // Build subdomain URL
            $host = $request->getHost();
            $parts = explode('.', $host);
            
            // If we're already on a subdomain, stay there
            if (count($parts) >= 3 || str_ends_with($host, '.test')) {
                // Already on subdomain, redirect to dashboard
                $role = $user->role ?? 'student';
                $redirectPath = match($role) {
                    'student' => '/student/dashboard',
                    'lecturer' => '/lecturer/dashboard',
                    'institution' => '/institution/dashboard',
                    default => '/student/dashboard',
                };
                
                return $request->wantsJson()
                    ? response()->json(['two_factor' => false, 'redirect' => $redirectPath])
                    : redirect()->intended($redirectPath);
            } else {
                // Redirect to institution subdomain
                $baseDomain = count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host;
                $scheme = $request->getScheme();
                $redirectUrl = "{$scheme}://{$institution->slug}.{$baseDomain}/dashboard";
                
                return $request->wantsJson()
                    ? response()->json(['two_factor' => false, 'redirect' => $redirectUrl])
                    : redirect($redirectUrl);
            }
        }
        
        // No active institution, redirect to home
        return $request->wantsJson()
            ? response()->json(['two_factor' => false, 'redirect' => '/'])
            : redirect('/');
    }
}

