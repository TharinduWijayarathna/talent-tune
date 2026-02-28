<?php

namespace App\Services\Application;

use App\Models\Batch;
use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use Illuminate\Support\Facades\DB;

class InstitutionDashboardService
{
    /**
     * Get dashboard stats and chart data for institution admin (scoped to institution).
     */
    public function getDashboardData(Institution $institution): array
    {
        $stats = [
            'totalLecturers' => User::forInstitution($institution->id)->where('role', 'lecturer')->count(),
            'totalStudents' => User::forInstitution($institution->id)->where('role', 'student')->count(),
            'activeBatches' => Batch::where('institution_id', $institution->id)->count(),
            'totalVivas' => Viva::where('institution_id', $institution->id)->count(),
        ];

        $charts = $this->getChartData($institution);

        return [
            'stats' => $stats,
            'charts' => $charts,
        ];
    }

    private function getChartData(Institution $institution): array
    {
        $vivasByStatus = $this->getVivasByStatus($institution);
        $usersByRole = $this->getUsersByRole($institution);
        $vivasOverTime = $this->getVivasOverTime($institution);

        return [
            'vivasByStatus' => $vivasByStatus,
            'usersByRole' => $usersByRole,
            'vivasOverTime' => $vivasOverTime,
        ];
    }

    private function getVivasByStatus(Institution $institution): array
    {
        $counts = Viva::where('institution_id', $institution->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
        $statusOrder = ['upcoming', 'active', 'completed'];

        return [
            'labels' => array_map(fn ($s) => ucfirst($s), $statusOrder),
            'series' => array_map(fn ($s) => (int) ($counts->get($s, 0)), $statusOrder),
        ];
    }

    private function getUsersByRole(Institution $institution): array
    {
        $counts = User::forInstitution($institution->id)
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

    private function getVivasOverTime(Institution $institution): array
    {
        $days = 30;
        $start = now()->subDays($days)->startOfDay();
        $rows = Viva::where('institution_id', $institution->id)
            ->where('created_at', '>=', $start)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $byDate = $rows->pluck('count', 'date');
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');
            $data[] = (int) ($byDate->get($d, 0));
        }

        return ['labels' => $labels, 'series' => [$data]];
    }
}
