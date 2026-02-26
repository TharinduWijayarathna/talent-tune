<?php

namespace App\Services\Admin;

use App\Models\Institution;
use App\Models\Payment;
use App\Models\User;
use App\Models\Viva;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Get dashboard stats, chart data, and recent activity for admin panel.
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
        $charts = $this->getChartData();

        return [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'charts' => $charts,
        ];
    }

    /**
     * Build chart data for dashboard analytics.
     */
    private function getChartData(): array
    {
        $revenueByDay = $this->getRevenueByDay(30);
        $usersByRole = $this->getUsersByRole();
        $vivasByStatus = $this->getVivasByStatus();
        $paymentsByStatus = $this->getPaymentsByStatus();

        return [
            'revenueByDay' => $revenueByDay,
            'usersByRole' => $usersByRole,
            'vivasByStatus' => $vivasByStatus,
            'paymentsByStatus' => $paymentsByStatus,
        ];
    }

    /**
     * Revenue (completed payments) per day for the last N days.
     */
    private function getRevenueByDay(int $days): array
    {
        $start = now()->subDays($days)->startOfDay();

        $rows = Payment::query()
            ->where('status', 'completed')
            ->where('paid_at', '>=', $start)
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byDate = $rows->pluck('total', 'date')->map(fn ($v) => (int) round($v / 100)); // cents to dollars

        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');
            $data[] = $byDate->get($d, 0);
        }

        return ['labels' => $labels, 'series' => [$data], 'totalRevenue' => array_sum($data)];
    }

    /**
     * User counts by role (for donut).
     */
    private function getUsersByRole(): array
    {
        $counts = User::query()
            ->whereIn('role', ['lecturer', 'student'])
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        return [
            'labels' => ['Lecturers', 'Students'],
            'series' => [
                (int) ($counts->get('lecturer', 0)),
                (int) ($counts->get('student', 0)),
            ],
        ];
    }

    /**
     * Viva counts by status (for bar chart).
     */
    private function getVivasByStatus(): array
    {
        $counts = Viva::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusOrder = ['upcoming', 'active', 'completed'];
        $labels = array_map(fn ($s) => ucfirst($s), $statusOrder);
        $series = array_map(fn ($s) => (int) ($counts->get($s, 0)), $statusOrder);

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * Payment counts by status (for donut).
     */
    private function getPaymentsByStatus(): array
    {
        $counts = Payment::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusOrder = ['completed', 'pending', 'failed', 'refunded'];
        $labels = array_map(fn ($s) => ucfirst($s), $statusOrder);
        $series = array_map(fn ($s) => (int) ($counts->get($s, 0)), $statusOrder);

        return ['labels' => $labels, 'series' => $series];
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
