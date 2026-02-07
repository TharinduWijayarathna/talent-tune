<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all users (admin only).
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->with('institution:id,name,slug')
            ->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
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

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }
}
