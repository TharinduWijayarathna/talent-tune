<?php

namespace App\Services\Application;

use App\Models\User;
use Illuminate\Http\Request;

class AuthRedirectService
{
    public function __construct(
        protected InstitutionResolverService $institutionResolver
    ) {}

    public function getRedirectUrl(User $user, Request $request): string
    {
        if ($user->role === 'admin') {
            return '/admin/dashboard';
        }

        $institution = $user->institution;

        if (!$institution || !$institution->is_active) {
            return '/';
        }

        $host = $request->getHost();
        $subdomain = $this->institutionResolver->extractSubdomain($host);

        if ($subdomain === $institution->slug) {
            return '/';
        }

        $baseDomain = $this->getBaseDomain($host);
        $scheme = $request->getScheme();

        return "{$scheme}://{$institution->slug}.{$baseDomain}/";
    }

    public function getBaseDomain(string $host): string
    {
        $parts = explode('.', $host);

        if (str_ends_with($host, '.test')) {
            if (count($parts) === 2) {
                return $host;
            }
            return implode('.', array_slice($parts, -2));
        }

        if (count($parts) >= 2) {
            return implode('.', array_slice($parts, -2));
        }

        return $host;
    }
}
