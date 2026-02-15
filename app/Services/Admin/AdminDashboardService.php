<?php

namespace App\Services\Admin;

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;

class AdminDashboardService
{
    /**
     * Get dashboard stats and recent activity for admin panel.
     */
    public function getDashboardData(): array
    {
        $stats = [
            'totalInstitutions' => Institution::count(),
            'totalLecturers' => User::where('role', 'lecturer')->count(),
            'totalStudents' => User::where('role', 'student')->count(),
            'activeVivas' => Viva::whereIn('status', ['upcoming', 'active'])->count(),
            'completedVivas' => Viva::where('status', 'completed')->count(),
            'totalUsers' => User::count(),
        ];

        $recentActivity = $this->getRecentActivity();

        return [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ];
    }

    /**
     * Build recent activity from latest vivas and user registrations.
     */
    private function getRecentActivity(): array
    {
        $activities = [];

        $recentVivas = Viva::with(['institution:id,name', 'lecturer:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentVivas as $viva) {
            $activities[] = [
                'id' => 'viva-'.$viva->id,
                'type' => $viva->status === 'completed' ? 'viva_completed' : 'viva_created',
                'message' => $viva->status === 'completed'
                    ? "{$viva->title} viva completed"
                    : "New viva: {$viva->title} by ".($viva->lecturer?->name ?? 'Unknown'),
                'time' => $viva->updated_at->diffForHumans(),
                'institution' => $viva->institution?->name ?? '—',
                '_sort_at' => $viva->updated_at->getTimestamp(),
            ];
        }

        $recentUsers = User::with('institution:id,name')
            ->where('role', '!=', 'admin')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentUsers as $user) {
            $activities[] = [
                'id' => 'user-'.$user->id,
                'type' => $user->role === 'student' ? 'student_added' : 'lecturer_added',
                'message' => "New {$user->role} registered: {$user->name}",
                'time' => $user->created_at->diffForHumans(),
                'institution' => $user->institution?->name ?? '—',
                '_sort_at' => $user->created_at->getTimestamp(),
            ];
        }

        return collect($activities)
            ->sortByDesc('_sort_at')
            ->take(10)
            ->map(fn ($item) => array_diff_key($item, ['_sort_at' => true]))
            ->values()
            ->all();
    }
}
