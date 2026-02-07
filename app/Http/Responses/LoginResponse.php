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

        if (! $user) {
            return redirect('/dashboard');
        }

        // For admin users, redirect directly (no institution context needed)
        if ($user->role === 'admin') {
            // Clear any intended URL to prevent conflicts
            $request->session()->forget('url.intended');

            // Redirect to admin dashboard
            if ($request->wantsJson()) {
                return response()->json([
                    'two_factor' => false,
                    'redirect' => '/admin/dashboard',
                ]);
            }

            return redirect('/admin/dashboard');
        }

        // For other users, check if they have an institution
        $institution = $user->institution;

        if ($institution && $institution->is_active) {
            $host = $request->getHost();
            $parts = explode('.', $host);
            $localTld = config('domain.local_tld', '.test');
            $onSubdomain = count($parts) >= 3 || ($localTld !== '' && str_ends_with($host, $localTld));

            if ($onSubdomain) {
                return $request->wantsJson()
                    ? response()->json(['two_factor' => false, 'redirect' => '/'])
                    : redirect()->intended('/');
            }

            $baseDomain = config('domain.domain');
            if ($baseDomain === null || $baseDomain === '') {
                $baseDomain = count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host;
            }
            $scheme = $request->getScheme();
            $redirectUrl = "{$scheme}://{$institution->slug}.{$baseDomain}/";

            return $request->wantsJson()
                ? response()->json(['two_factor' => false, 'redirect' => $redirectUrl])
                : redirect($redirectUrl);
        }

        // No active institution, redirect to home
        return $request->wantsJson()
            ? response()->json(['two_factor' => false, 'redirect' => '/'])
            : redirect('/');
    }
}
