<?php

namespace App\Services\Application;

use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionResolverService
{
    public function resolve(Request $request): ?Institution
    {
        $institution = $request->attributes->get('institution');

        if ($institution instanceof Institution) {
            return $institution;
        }

        $subdomain = $this->extractSubdomain($request->getHost());

        if ($subdomain && ! $this->isReservedSubdomain($subdomain)) {
            return Institution::where('slug', $subdomain)->first();
        }

        return null;
    }

    public function resolveActive(Request $request): ?Institution
    {
        $subdomain = $this->extractSubdomain($request->getHost());

        if ($subdomain && ! $this->isReservedSubdomain($subdomain)) {
            return Institution::where('slug', $subdomain)->active()->first();
        }

        return null;
    }

    public function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);
        $localTld = config('domain.local_tld', '.test');

        if (count($parts) >= 3) {
            return $parts[0];
        }
        if (count($parts) === 2 && $localTld !== '' && str_ends_with($host, $localTld)) {
            return $parts[0];
        }

        return null;
    }

    protected function isReservedSubdomain(string $subdomain): bool
    {
        return in_array($subdomain, config('domain.reserved_subdomains', []), true);
    }
}
