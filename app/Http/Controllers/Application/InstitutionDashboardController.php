<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use App\Services\Application\InstitutionDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionDashboardController extends Controller
{
    public function __construct(
        protected InstitutionDashboardService $dashboardService
    ) {}

    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (! $institution) {
            abort(403, 'Institution context required.');
        }

        return $institution;
    }

    protected function authorizeInstitution(?User $user, ?Institution $institution): void
    {
        if (! $user) {
            abort(403);
        }
        if ($user->role === 'admin') {
            return;
        }
        if ($user->role !== 'institution' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    public function index(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $data = $this->dashboardService->getDashboardData($institution);

        return Inertia::render('institution/Dashboard', [
            'stats' => $data['stats'],
            'charts' => $data['charts'],
        ]);
    }
}
