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

        if (! $institution || ! $institution->is_active) {
            return '/';
        }

        $baseDomain = $this->getBaseDomainFromRequest($request);
        $scheme = $request->getScheme();
        $baseUrl = "{$scheme}://{$institution->slug}.{$baseDomain}";

        if ($institution->subscription_status !== 'active') {
            return $baseUrl.'/institution/complete-subscription';
        }

        $host = $request->getHost();
        $subdomain = $this->institutionResolver->extractSubdomain($host);

        if ($subdomain === $institution->slug) {
            return '/';
        }

        return $baseUrl.'/';
    }

    /**
     * Get the base domain for building URLs. Uses APP_DOMAIN when set, otherwise derives from host.
     */
    public function getBaseDomainFromRequest(Request $request): string
    {
        $configured = config('domain.domain');
        if ($configured !== null && $configured !== '') {
            return $configured;
        }

        return $this->getBaseDomain($request->getHost());
    }

    /**
     * Derive base domain from a host string (e.g. acme.example.com -> example.com).
     */
    public function getBaseDomain(string $host): string
    {
        $parts = explode('.', $host);
        $localTld = config('domain.local_tld', '.test');

        if ($localTld !== '' && str_ends_with($host, $localTld)) {
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
