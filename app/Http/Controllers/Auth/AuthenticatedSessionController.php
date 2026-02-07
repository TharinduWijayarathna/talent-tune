<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Application\AuthRedirectService;
use App\Services\Application\InstitutionResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected InstitutionResolverService $institutionResolver,
        protected AuthRedirectService $authRedirectService
    ) {}

    /**
     * Display the login view.
     */
    public function create(Request $request)
    {
        $institution = $request->attributes->get('institution');

        if (! $institution) {
            $institution = $this->institutionResolver->resolveActive($request);
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
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        try {
            $request->merge(['role' => $request->input('role') ?: null]);

            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
                'role' => ['nullable', 'string', 'in:student,lecturer,institution,admin'],
            ]);

            if (! Auth::attempt(
                $request->only('email', 'password'),
                $request->boolean('remember')
            )) {
                throw ValidationException::withMessages([
                    'email' => __('These credentials do not match our records.'),
                ]);
            }

            $request->session()->regenerate();

            $user = Auth::user();

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

            $request->session()->forget('url.intended');

            $redirectUrl = $this->authRedirectService->getRedirectUrl($user, $request);

            if ($request->wantsJson()) {
                return response()->json([
                    'two_factor' => false,
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect()->intended($redirectUrl);
        } catch (ValidationException $e) {
            $institution = $request->attributes->get('institution');
            if (! $institution) {
                $institution = $this->institutionResolver->resolveActive($request);
            }
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
}
