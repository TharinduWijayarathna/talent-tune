<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $institution = $request->attributes->get('institution');

        // Base domain for institution URLs (from config or derived from request)
        $baseDomain = config('domain.domain');
        if ($baseDomain === null || $baseDomain === '') {
            $host = $request->getHost();
            $parts = explode('.', $host);
            $localTld = config('domain.local_tld', '.test');
            $baseDomain = ($localTld !== '' && str_ends_with($host, $localTld))
                ? (count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host)
                : (count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host);
        }

        $user = $request->user();
        $authUser = $user ? array_merge($user->toArray(), [
            'role' => $user->role,
        ]) : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $authUser,
            ],
            'institution' => $institution ? [
                'id' => $institution->id,
                'name' => $institution->name,
                'slug' => $institution->slug,
                'logo_url' => $institution->logo_url,
                'primary_color' => $institution->primary_color,
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'csrfToken' => $request->session()->token(),
            'baseDomain' => $baseDomain,
        ];
    }
}
