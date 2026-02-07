<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Services\Application\DashboardService;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Redirect the user to the appropriate dashboard based on their role.
     */
    public function redirect(): RedirectResponse
    {
        return $this->dashboardService->getRedirectForCurrentUser();
    }
}
