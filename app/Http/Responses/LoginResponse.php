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
        
        // Redirect based on user role
        $role = $user->role ?? 'student';
        $redirectPath = match($role) {
            'student' => '/student/dashboard',
            'lecturer' => '/lecturer/dashboard',
            'institution' => '/institution/dashboard',
            'admin' => '/admin/dashboard',
            default => '/student/dashboard',
        };
        
        return $request->wantsJson()
            ? response()->json(['two_factor' => false, 'redirect' => $redirectPath])
            : redirect()->intended($redirectPath);
    }
}

