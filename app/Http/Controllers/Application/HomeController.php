<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Services\Application\InstitutionResolverService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __construct(
        protected InstitutionResolverService $institutionResolver
    ) {}

    /**
     * Show the appropriate home page: institution pending, institution homepage, or public landing.
     */
    public function index(Request $request): Response
    {
        $institution = $this->institutionResolver->resolve($request);

        if ($institution) {
            if (! $institution->is_active) {
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

            if ($institution->subscription_status !== 'active') {
                return Inertia::render('home/InstitutionPaymentRequired', [
                    'institution' => [
                        'name' => $institution->name,
                        'slug' => $institution->slug,
                        'login_url' => url('/login'),
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
}
