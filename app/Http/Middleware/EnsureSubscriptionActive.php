<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    protected array $allowedRoutes = [
        'institution.complete-subscription',
        'institution.complete-subscription.checkout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->role !== 'institution') {
            return $next($request);
        }
        if ($request->routeIs($this->allowedRoutes)) {
            return $next($request);
        }
        $institution = $user->institution;
        if (! $institution || $institution->subscription_status === 'active') {
            return $next($request);
        }

        return redirect()->route('institution.complete-subscription');
    }
}
