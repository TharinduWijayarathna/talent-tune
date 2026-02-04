<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InstitutionUserController extends Controller
{
    /**
     * Get the current institution from request (set by SetInstitutionContext middleware).
     */
    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (!$institution) {
            abort(403, 'Institution context required.');
        }
        return $institution;
    }

    /**
     * List lecturers for the current institution only.
     */
    public function lecturers(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturers = User::forInstitution($institution->id)
            ->where('role', 'lecturer')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'employee_id' => $u->employee_id,
                'department' => $u->department,
                'status' => 'active',
                'totalSessions' => 0,
                'created_at' => $u->created_at->toISOString(),
            ]);

        return Inertia::render('institution/Lecturers', [
            'lecturers' => $lecturers,
        ]);
    }

    /**
     * List students for the current institution only.
     */
    public function students(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $students = User::forInstitution($institution->id)
            ->where('role', 'student')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'student_id' => $u->student_id,
                'batch' => $u->batch,
                'department' => $u->department,
                'status' => 'active',
                'completedVivas' => 0,
                'created_at' => $u->created_at->toISOString(),
            ]);

        return Inertia::render('institution/Students', [
            'students' => $students,
        ]);
    }

    /**
     * Show add lecturer form.
     */
    public function createLecturer(Request $request)
    {
        $this->institution($request);
        $this->authorizeInstitution($request->user(), $request->attributes->get('institution'));

        return Inertia::render('institution/AddLecturer');
    }

    /**
     * Store a new lecturer for the current institution.
     */
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

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'lecturer',
            'institution_id' => $institution->id,
            'department' => $validated['department'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
        ]);

        return redirect()->route('institution.lecturers')->with('status', 'Lecturer added successfully.');
    }

    /**
     * Show add student form.
     */
    public function createStudent(Request $request)
    {
        $this->institution($request);
        $this->authorizeInstitution($request->user(), $request->attributes->get('institution'));

        return Inertia::render('institution/AddStudent');
    }

    /**
     * Store a new student for the current institution.
     */
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

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'institution_id' => $institution->id,
            'student_id' => $validated['student_id'] ?? null,
            'batch' => $validated['batch'] ?? null,
            'department' => $validated['department'] ?? null,
        ]);

        return redirect()->route('institution.students')->with('status', 'Student added successfully.');
    }

    /**
     * Show edit lecturer form (only for lecturers belonging to current institution).
     */
    public function editLecturer(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturer = User::forInstitution($institution->id)
            ->where('role', 'lecturer')
            ->findOrFail($id);

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

    /**
     * Update a lecturer (only if belongs to current institution).
     */
    public function updateLecturer(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturer = User::forInstitution($institution->id)
            ->where('role', 'lecturer')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($lecturer->id)],
            'department' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
        ]);

        $lecturer->update($validated);

        return redirect()->route('institution.lecturers')->with('status', 'Lecturer updated successfully.');
    }

    /**
     * Show edit student form (only for students belonging to current institution).
     */
    public function editStudent(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $student = User::forInstitution($institution->id)
            ->where('role', 'student')
            ->findOrFail($id);

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
        ]);
    }

    /**
     * Update a student (only if belongs to current institution).
     */
    public function updateStudent(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $student = User::forInstitution($institution->id)
            ->where('role', 'student')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->id)],
            'student_id' => ['nullable', 'string', 'max:255'],
            'batch' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $student->update($validated);

        return redirect()->route('institution.students')->with('status', 'Student updated successfully.');
    }

    /**
     * Delete a lecturer (only if belongs to current institution).
     */
    public function destroyLecturer(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $lecturer = User::forInstitution($institution->id)
            ->where('role', 'lecturer')
            ->findOrFail($id);

        $lecturer->delete();

        return redirect()->route('institution.lecturers')->with('status', 'Lecturer removed.');
    }

    /**
     * Delete a student (only if belongs to current institution).
     */
    public function destroyStudent(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $student = User::forInstitution($institution->id)
            ->where('role', 'student')
            ->findOrFail($id);

        $student->delete();

        return redirect()->route('institution.students')->with('status', 'Student removed.');
    }

    /**
     * Ensure the user can manage this institution (must be institution admin or admin).
     */
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
