<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use App\Services\Application\InstitutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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

    /**
     * Show the form for editing a user.
     */
    public function edit(Request $request, User $user)
    {
        $institutions = $this->institutionService->getListForAdmin();

        return Inertia::render('admin/EditUser', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'institution_id' => $user->institution_id,
                'student_id' => $user->student_id,
                'employee_id' => $user->employee_id,
                'batch' => $user->batch,
                'department' => $user->department,
            ],
            'institutions' => $institutions,
            'returnSection' => $request->query('return', 'students'),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['student', 'lecturer', 'institution', 'admin'])],
            'institution_id' => ['nullable', 'integer', 'exists:institutions,id'],
            'student_id' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'batch' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $this->adminUserService->updateUser($user, $validated);

        $returnSection = $request->input('return_section', 'students');
        $routeMap = [
            'institution_admins' => 'admin.users.institution-admins',
            'students' => 'admin.users.students',
            'lecturers' => 'admin.users.lecturers',
        ];
        $route = $routeMap[$returnSection] ?? 'admin.users.students';

        return redirect()->route($route)->with('status', 'User updated successfully.');
    }
}
