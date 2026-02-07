<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Institution;
use App\Models\User;
use App\Services\Application\AuthRedirectService;
use App\Services\Application\InstitutionUserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InstitutionUserController extends Controller
{
    public function __construct(
        protected InstitutionUserService $institutionUserService,
        protected AuthRedirectService $authRedirectService
    ) {}

    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (!$institution) {
            abort(403, 'Institution context required.');
        }
        return $institution;
    }

    public function lecturers(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturers = $this->institutionUserService->getLecturers($institution);

        return Inertia::render('institution/Lecturers', [
            'lecturers' => $lecturers,
        ]);
    }

    public function students(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $students = $this->institutionUserService->getStudents($institution);

        return Inertia::render('institution/Students', [
            'students' => $students,
        ]);
    }

    public function createLecturer(Request $request)
    {
        $this->institution($request);
        $this->authorizeInstitution($request->user(), $request->attributes->get('institution'));

        return Inertia::render('institution/AddLecturer');
    }

    public function storeLecturer(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'department' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
        ]);

        $baseDomain = $this->authRedirectService->getBaseDomain($request->getHost());
        $scheme = $request->getScheme();
        $this->institutionUserService->createLecturer($institution, $validated, $baseDomain, $scheme);

        return redirect()->route('institution.lecturers')->with('status', 'Lecturer added successfully. Credentials have been sent to their email.');
    }

    public function createStudent(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $batches = Batch::where('institution_id', $institution->id)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return Inertia::render('institution/AddStudent', [
            'batches' => $batches,
        ]);
    }

    public function storeStudent(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'student_id' => ['nullable', 'string', 'max:255'],
            'batch' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $baseDomain = $this->authRedirectService->getBaseDomain($request->getHost());
        $scheme = $request->getScheme();
        $this->institutionUserService->createStudent($institution, $validated, $baseDomain, $scheme);

        return redirect()->route('institution.students')->with('status', 'Student added successfully. Credentials have been sent to their email.');
    }

    public function editLecturer(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturer = $this->institutionUserService->getLecturerForEdit($institution, $id);

        return Inertia::render('institution/EditLecturer', [
            'lecturerId' => $lecturer->id,
            'lecturer' => [
                'id' => $lecturer->id,
                'name' => $lecturer->name,
                'email' => $lecturer->email,
                'employee_id' => $lecturer->employee_id,
                'department' => $lecturer->department,
            ],
        ]);
    }

    public function updateLecturer(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturer = $this->institutionUserService->getLecturerForEdit($institution, $id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($lecturer->id)],
            'department' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
        ]);

        $this->institutionUserService->updateLecturer($lecturer, $validated);

        return redirect()->route('institution.lecturers')->with('status', 'Lecturer updated successfully.');
    }

    public function editStudent(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $student = $this->institutionUserService->getStudentForEdit($institution, $id);

        $batches = Batch::where('institution_id', $institution->id)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return Inertia::render('institution/EditStudent', [
            'studentId' => $student->id,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'student_id' => $student->student_id,
                'batch' => $student->batch,
                'department' => $student->department,
            ],
            'batches' => $batches,
        ]);
    }

    public function updateStudent(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $student = $this->institutionUserService->getStudentForEdit($institution, $id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->id)],
            'student_id' => ['nullable', 'string', 'max:255'],
            'batch' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $this->institutionUserService->updateStudent($student, $validated);

        return redirect()->route('institution.students')->with('status', 'Student updated successfully.');
    }

    public function destroyLecturer(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturer = $this->institutionUserService->getLecturerForEdit($institution, $id);
        $this->institutionUserService->destroyLecturer($lecturer);

        return redirect()->route('institution.lecturers')->with('status', 'Lecturer removed.');
    }

    public function destroyStudent(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $student = $this->institutionUserService->getStudentForEdit($institution, $id);
        $this->institutionUserService->destroyStudent($student);

        return redirect()->route('institution.students')->with('status', 'Student removed.');
    }

    protected function authorizeInstitution(?User $user, ?Institution $institution): void
    {
        if (!$user) {
            abort(403);
        }
        if ($user->role === 'admin') {
            return;
        }
        if ($user->role !== 'institution' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }
}
