<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AdminDashboardService $adminDashboardService
    ) {}

    /**
     * Display the admin dashboard with real stats and recent activity.
     */
    public function index(): Response
    {
        $data = $this->adminDashboardService->getDashboardData();

        return Inertia::render('admin/Dashboard', [
            'stats' => $data['stats'],
            'recentActivity' => $data['recentActivity'],
        ]);
    }
}
