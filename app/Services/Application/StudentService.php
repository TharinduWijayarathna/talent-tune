<?php

namespace App\Services\Application;

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use App\Services\Ai\RubricService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    public function __construct(
        protected RubricService $rubricService
    ) {}

    /**
     * Get dashboard stats and upcoming vivas for the student (scoped by institution and student's batch).
     */
    public function getDashboardData(Institution $institution, User $user): array
    {
        $batch = $user->batch;

        $upcomingCount = 0;
        $totalSessions = 0;
        $upcomingVivas = [];

        if ($batch) {
            $upcomingCount = Viva::where('institution_id', $institution->id)
                ->where('batch', $batch)
                ->where('status', 'upcoming')
                ->count();

            $totalSessions = Viva::where('institution_id', $institution->id)
                ->where('batch', $batch)
                ->count();

            $upcomingList = Viva::where('institution_id', $institution->id)
                ->where('batch', $batch)
                ->where('status', 'upcoming')
                ->with('lecturer')
                ->orderBy('scheduled_at')
                ->limit(10)
                ->get();
            $upcomingVivas = $upcomingList->map(fn (Viva $v) => [
                'id' => $v->id,
                'title' => $v->title,
                'date' => $v->scheduled_at->format('Y-m-d'),
                'time' => $v->scheduled_at->format('g:i A'),
                'lecturer' => $v->lecturer->name,
            ])->all();
        }

        $completedSubmissions = VivaStudentSubmission::where('student_id', $user->id)
            ->where('status', 'completed');
        $completedCount = $completedSubmissions->count();

        $stats = [
            'upcomingVivas' => $upcomingCount,
            'completedVivas' => $completedCount,
            'totalSessions' => $totalSessions,
        ];

        return [
            'stats' => $stats,
            'upcomingVivas' => $upcomingVivas,
        ];
    }

    /**
     * Get viva sessions for the student (only vivas in their batch).
     */
    public function getVivaSessions(Institution $institution, User $user): array
    {
        if (!$user->batch) {
            return [];
        }

        $vivas = Viva::where('institution_id', $institution->id)
            ->where('batch', $user->batch)
            ->with('lecturer')
            ->orderBy('scheduled_at', 'desc')
            ->get();

        $submissionScores = VivaStudentSubmission::where('student_id', $user->id)
            ->whereIn('viva_id', $vivas->pluck('id'))
            ->get()
            ->keyBy('viva_id');

        // Compare in UTC: stored scheduled_at is in UTC
        $nowUtc = now()->utc();

        return $vivas->map(function (Viva $viva) use ($submissionScores, $nowUtc) {
            $submission = $submissionScores->get($viva->id);
            $marks = $submission && $submission->status === 'completed' ? $submission->total_score : null;

            $rawScheduled = $viva->getRawOriginal('scheduled_at');
            $scheduledAtUtc = $rawScheduled ? Carbon::parse($rawScheduled, 'UTC') : $viva->scheduled_at->copy()->utc();
            $scheduledReached = $scheduledAtUtc->lte($nowUtc);
            $notClosed = $viva->status !== 'completed';
            $can_attend = $scheduledReached && $notClosed;

            return [
                'id' => $viva->id,
                'title' => $viva->title,
                'description' => $viva->description,
                'date' => $viva->scheduled_at->format('Y-m-d'),
                'time' => $viva->scheduled_at->format('g:i A'),
                'scheduled_at' => $scheduledAtUtc->toIso8601String(),
                'lecturer' => $viva->lecturer->name,
                'status' => $viva->status,
                'batch' => $viva->batch,
                'materials' => $viva->lecture_materials,
                'marks' => $marks,
                'can_attend' => $can_attend,
            ];
        })->all();
    }

    /**
     * Get viva for attend. Student may attend only on/after scheduled date and until lecturer closes the viva.
     */
    public function getVivaForAttend(Institution $institution, User $user, int $id): array
    {
        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $id)
            ->where('batch', $user->batch)
            ->with('lecturer')
            ->firstOrFail();

        if ($viva->status === 'completed') {
            abort(403, 'This viva has been closed by the lecturer. You can no longer attend.');
        }

        $rawScheduled = $viva->getRawOriginal('scheduled_at');
        $scheduledAtUtc = $rawScheduled ? Carbon::parse($rawScheduled, 'UTC') : $viva->scheduled_at->copy()->utc();
        if ($scheduledAtUtc->isFuture()) {
            abort(403, 'This viva opens on ' . $scheduledAtUtc->format('M j, Y \a\t g:i A') . ' UTC. You cannot attend before the scheduled date and time.');
        }

        $submission = VivaStudentSubmission::firstOrCreate(
            [
                'viva_id' => $viva->id,
                'student_id' => $user->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        return [
            'viva' => [
                'id' => $viva->id,
                'title' => $viva->title,
                'description' => $viva->description,
                'instructions' => $viva->instructions,
                'scheduled_at' => $viva->scheduled_at->format('Y-m-d H:i'),
                'lecturer' => $viva->lecturer->name,
            ],
            'submission' => [
                'id' => $submission->id,
                'document_path' => $submission->document_path,
                'status' => $submission->status,
            ],
        ];
    }

    public function uploadVivaDocument(Institution $institution, User $user, int $vivaId, UploadedFile $document): array
    {
        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $vivaId)
            ->where('batch', $user->batch)
            ->firstOrFail();

        $submission = VivaStudentSubmission::firstOrCreate(
            [
                'viva_id' => $viva->id,
                'student_id' => $user->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        if ($submission->document_path && Storage::exists($submission->document_path)) {
            Storage::delete($submission->document_path);
        }

        $path = $document->store('vivas/student-documents', 'private');
        $submission->document_path = $path;
        $submission->status = 'in_progress';
        $submission->save();

        return [
            'success' => true,
            'message' => 'Document uploaded successfully',
            'document_path' => $path,
        ];
    }

    public function completeVivaSubmission(User $user, array $validated): array
    {
        $submission = VivaStudentSubmission::where('id', $validated['submission_id'])
            ->where('student_id', $user->id)
            ->firstOrFail();

        $answers = $validated['answers'];
        $scores = array_map(fn ($a) => (int) $a['score_1_10'], $answers);

        $submission->answers = $answers;
        $submission->status = 'completed';

        $rubric = $this->rubricService->getRubricScore($scores);
        if ($rubric['success']) {
            $submission->total_score = (int) round($rubric['score']);
            $submission->feedback = null;
        } else {
            $submission->total_score = (int) round(array_sum($scores) / 5 * 10);
            $submission->feedback = 'Rubric service unavailable; score is average of question scores.';
        }

        $submission->save();

        return [
            'success' => true,
            'rubric_score' => $submission->total_score,
            'rubric_from_service' => $rubric['success'],
        ];
    }
}
