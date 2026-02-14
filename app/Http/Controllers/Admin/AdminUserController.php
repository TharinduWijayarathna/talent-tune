<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminUserService;
use App\Services\Application\InstitutionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class AdminUserController extends Controller
{
    public function __construct(
        protected AdminUserService $adminUserService,
        protected InstitutionService $institutionService
    ) {}

    /**
     * Redirect to institutional student management (default user management section).
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.users.students');
    }

    /**
     * Institution admin management – users with role "institution", institution-wise.
     */
    public function institutionAdmins(Request $request)
    {
        $data = $this->adminUserService->getUsersWithFilters($request, 'institution');
        $institutions = $this->institutionService->getListForAdmin();

        return Inertia::render('admin/Users', [
            'users' => $data['users'],
            'filters' => $data['filters'],
            'institutions' => $institutions,
            'section' => 'institution_admins',
        ]);
    }

    /**
     * Institutional student management – students, institution-wise.
     */
    public function students(Request $request)
    {
        $data = $this->adminUserService->getUsersWithFilters($request, 'student');
        $institutions = $this->institutionService->getListForAdmin();

        return Inertia::render('admin/Users', [
            'users' => $data['users'],
            'filters' => $data['filters'],
            'institutions' => $institutions,
            'section' => 'students',
        ]);
    }

    /**
     * Institutional lecturer management – lecturers, institution-wise.
     */
    public function lecturers(Request $request)
    {
        $data = $this->adminUserService->getUsersWithFilters($request, 'lecturer');
        $institutions = $this->institutionService->getListForAdmin();

        return Inertia::render('admin/Users', [
            'users' => $data['users'],
            'filters' => $data['filters'],
            'institutions' => $institutions,
            'section' => 'lecturers',
        ]);
    }
}
