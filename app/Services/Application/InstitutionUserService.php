<?php

namespace App\Services\Application;

use App\Models\Institution;
use App\Models\User;
use App\Notifications\UserCredentialsSent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class InstitutionUserService
{
    public function getLecturers(Institution $institution): array
    {
        return User::forInstitution($institution->id)
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
            ])
            ->all();
    }

    public function getStudents(Institution $institution): array
    {
        return User::forInstitution($institution->id)
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
            ])
            ->all();
    }

    /**
     * Create a lecturer. If $baseDomain is provided, sends credentials email to the new user.
     */
    public function createLecturer(Institution $institution, array $validated, ?string $baseDomain = null, string $scheme = 'https'): User
    {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'lecturer',
            'institution_id' => $institution->id,
            'department' => $validated['department'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
        ]);

        if ($baseDomain !== null) {
            Notification::route('mail', $user->email)
                ->notify(new UserCredentialsSent(
                    $institution,
                    $user->name,
                    $user->email,
                    $validated['password'],
                    'lecturer',
                    $baseDomain,
                    $scheme
                ));
        }

        return $user;
    }

    /**
     * Create a student. If $baseDomain is provided, sends credentials email to the new user.
     */
    public function createStudent(Institution $institution, array $validated, ?string $baseDomain = null, string $scheme = 'https'): User
    {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'institution_id' => $institution->id,
            'student_id' => $validated['student_id'] ?? null,
            'batch' => $validated['batch'] ?? null,
            'department' => $validated['department'] ?? null,
        ]);

        if ($baseDomain !== null) {
            Notification::route('mail', $user->email)
                ->notify(new UserCredentialsSent(
                    $institution,
                    $user->name,
                    $user->email,
                    $validated['password'],
                    'student',
                    $baseDomain,
                    $scheme
                ));
        }

        return $user;
    }

    public function getLecturerForEdit(Institution $institution, int $id): User
    {
        return User::forInstitution($institution->id)
            ->where('role', 'lecturer')
            ->findOrFail($id);
    }

    public function getStudentForEdit(Institution $institution, int $id): User
    {
        return User::forInstitution($institution->id)
            ->where('role', 'student')
            ->findOrFail($id);
    }

    public function updateLecturer(User $lecturer, array $validated): void
    {
        $lecturer->update($validated);
    }

    public function updateStudent(User $student, array $validated): void
    {
        $student->update($validated);
    }

    public function destroyLecturer(User $lecturer): void
    {
        $lecturer->delete();
    }

    public function destroyStudent(User $student): void
    {
        $student->delete();
    }
}
