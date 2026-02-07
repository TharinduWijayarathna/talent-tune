<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
class HomeController extends Controller
{
    /**
     * Show the appropriate home page: institution pending, institution homepage, or public landing.
     */
    public function index(Request $request): Response
    {
        $institution = $this->resolveInstitution($request);

        if ($institution) {
            if (!$institution->is_active) {
                return Inertia::render('home/InstitutionPending', [
                    'institution' => [
                        'id' => $institution->id,
                        'name' => $institution->name,
                        'slug' => $institution->slug,
                        'email' => $institution->email,
                        'contact_person' => $institution->contact_person,
                    ],
                ]);
            }

            return Inertia::render('home/HomePage', [
                'canRegister' => Features::enabled(Features::registration()),
                'institution' => [
                    'id' => $institution->id,
                    'name' => $institution->name,
                    'slug' => $institution->slug,
                    'logo_url' => $institution->logo_url,
                    'primary_color' => $institution->primary_color,
                ],
            ]);
        }

        return Inertia::render('home/LandingPage', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    /**
     * Resolve institution from request attributes (middleware) or from host/subdomain.
     */
    protected function resolveInstitution(Request $request): ?Institution
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
}
