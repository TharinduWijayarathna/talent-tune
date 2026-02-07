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

        $host = $request->getHost();
        $parts = explode('.', $host);

        $subdomain = null;
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
        } elseif (count($parts) === 2 && str_ends_with($host, '.test')) {
            $subdomain = $parts[0];
        }

        if ($subdomain && !in_array($subdomain, ['www', 'app', 'talenttune'], true)) {
            return Institution::where('slug', $subdomain)->first();
        }

        return null;
    }

    public function resolveActive(Request $request): ?Institution
    {
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain && !in_array($subdomain, ['www', 'app', 'talenttune'], true)) {
            return Institution::where('slug', $subdomain)->active()->first();
        }

        return null;
    }

    public function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            return $parts[0];
        }
        if (count($parts) === 2 && str_ends_with($host, '.test')) {
            return $parts[0];
        }

        return null;
    }
}
