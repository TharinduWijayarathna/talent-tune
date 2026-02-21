<?php

namespace App\Services\Application;

use App\Models\Batch;
use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use Illuminate\Support\Carbon;

class LecturerService
{

    public function getDashboardData(Institution $institution, User $user): array
    {
        $stats = [
            'totalSessions' => Viva::where('institution_id', $institution->id)
                ->where('lecturer_id', $user->id)
                ->count(),
            'activeSessions' => Viva::where('institution_id', $institution->id)
                ->where('lecturer_id', $user->id)
                ->where('status', 'upcoming')
                ->count(),
            'totalStudents' => User::forInstitution($institution->id)
                ->where('role', 'student')
                ->count(),
            'completedSessions' => Viva::where('institution_id', $institution->id)
                ->where('lecturer_id', $user->id)
                ->where('status', 'completed')
                ->count(),
        ];

        $recentSessions = Viva::where('institution_id', $institution->id)
            ->where('lecturer_id', $user->id)
            ->orderBy('scheduled_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($viva) use ($institution) {
                $studentsInBatch = User::forInstitution($institution->id)
                    ->where('role', 'student')
                    ->where('batch', $viva->batch)
                    ->count();

                return [
                    'id' => $viva->id,
                    'title' => $viva->title,
                    'batch' => $viva->batch,
                    'date' => $viva->scheduled_at->format('Y-m-d'),
                    'status' => $viva->status,
                    'students' => $studentsInBatch,
                ];
            })
            ->all();

        return [
            'stats' => $stats,
            'recentSessions' => $recentSessions,
        ];
    }

    public function getVivas(Institution $institution, User $user): array
    {
        return Viva::where('institution_id', $institution->id)
            ->where('lecturer_id', $user->id)
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function ($viva) use ($institution) {
                $studentsInBatch = User::forInstitution($institution->id)
                    ->where('role', 'student')
                    ->where('batch', $viva->batch)
                    ->count();

                return [
                    'id' => $viva->id,
                    'title' => $viva->title,
                    'description' => $viva->description,
                    'batch' => $viva->batch,
                    'scheduled_at' => $viva->scheduled_at->format('Y-m-d H:i'),
                    'status' => $viva->status,
                    'students' => $studentsInBatch,
                ];
            })
            ->all();
    }

    /**
     * Get batch names for the institution (from Batch model; fallback to distinct student batches).
     */
    public function getBatchesForInstitution(Institution $institution): array
    {
        $fromBatches = Batch::where('institution_id', $institution->id)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        if (! empty($fromBatches)) {
            return $fromBatches;
        }

        return User::forInstitution($institution->id)
            ->where('role', 'student')
            ->whereNotNull('batch')
            ->distinct()
            ->pluck('batch')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Create a viva session. Lecturer's instructions are stored and used at question-generation time.
     */
    public function createViva(Institution $institution, User $user, array $validated): Viva
    {
        // Parse scheduled time in lecturer's timezone so "6:30 PM" means their local time, then store as UTC
        $tz = ! empty($validated['timezone']) && in_array($validated['timezone'], timezone_identifiers_list(), true)
            ? $validated['timezone']
            : config('app.timezone');
        $scheduledAt = Carbon::parse($validated['date'].' '.$validated['time'], $tz)->utc();

        return Viva::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'batch' => $validated['batch'],
            'scheduled_at' => $scheduledAt,
            'instructions' => $validated['instructions'] ?? null,
            'lecture_materials' => null,
            'viva_background' => null,
            'base_prompt' => null,
            'institution_id' => $institution->id,
            'lecturer_id' => $user->id,
            'status' => 'upcoming',
        ]);
    }

    public function getVivaForShow(Institution $institution, User $user, int $id): Viva
    {
        return Viva::where('institution_id', $institution->id)
            ->where('lecturer_id', $user->id)
            ->findOrFail($id);
    }

    /**
     * Get all submissions (attendees) for a viva for lecturer view.
     *
     * @return array<int, array{id: int, student_name: string, status: string, total_score: int|null, grade: string|null, feedback: string|null, answers: array, document_path: string|null, completed_at: string|null}>
     */
    public function getVivaSubmissionsForShow(Institution $institution, User $user, int $vivaId): array
    {
        $viva = Viva::where('institution_id', $institution->id)
            ->where('lecturer_id', $user->id)
            ->findOrFail($vivaId);

        return VivaStudentSubmission::where('viva_id', $viva->id)
            ->with('student')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (VivaStudentSubmission $sub) {
                return [
                    'id' => $sub->id,
                    'student_name' => $sub->student?->name ?? 'Unknown',
                    'student_email' => $sub->student?->email ?? null,
                    'status' => $sub->status,
                    'total_score' => $sub->total_score,
                    'grade' => $sub->grade,
                    'feedback' => $sub->feedback,
                    'answers' => $sub->answers ?? [],
                    'document_path' => $sub->document_path,
                    'completed_at' => $sub->updated_at?->format('Y-m-d H:i'),
                ];
            })
            ->all();
    }

    /**
     * Close a viva so students can no longer attend. Only the owning lecturer can close.
     */
    public function closeViva(Institution $institution, User $user, int $id): Viva
    {
        $viva = Viva::where('institution_id', $institution->id)
            ->where('lecturer_id', $user->id)
            ->findOrFail($id);

        $viva->update(['status' => 'completed']);

        return $viva->fresh();
    }
}
