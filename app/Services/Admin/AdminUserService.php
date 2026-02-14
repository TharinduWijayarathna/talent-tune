<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserService
{
    /**
     * Get paginated users with filters.
     *
     * @param  array{role?: string}  $roleOverride  If set, overrides request role (for section-specific routes).
     */
    public function getUsersWithFilters(Request $request, ?string $roleOverride = null): array
    {
        $query = User::query()
            ->with('institution:id,name,slug')
            ->latest();

        $role = $roleOverride ?? $request->input('role');
        if ($role !== null && $role !== '') {
            $query->where('role', $role);
        }

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'student_id' => $user->student_id,
                'employee_id' => $user->employee_id,
                'batch' => $user->batch,
                'department' => $user->department,
                'institution' => $user->institution
                    ? [
                        'id' => $user->institution->id,
                        'name' => $user->institution->name,
                        'slug' => $user->institution->slug,
                    ]
                    : null,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
            ];
        });

        $filters = $request->only(['search', 'institution_id']);
        if ($roleOverride === null) {
            $filters['role'] = $request->input('role');
        } else {
            $filters['role'] = $roleOverride;
        }

        return [
            'users' => $users,
            'filters' => $filters,
        ];
    }

    /**
     * Update a user (admin only). Password is only updated if provided.
     */
    public function updateUser(User $user, array $data): void
    {
        $fillable = [
            'name',
            'email',
            'role',
            'institution_id',
            'student_id',
            'employee_id',
            'batch',
            'department',
        ];

        $update = array_intersect_key($data, array_flip($fillable));
        if (isset($data['institution_id']) && $data['institution_id'] === '') {
            $update['institution_id'] = null;
        }

        if (! empty($data['password'] ?? null)) {
            $update['password'] = $data['password'];
        }

        $user->update($update);
    }
}
