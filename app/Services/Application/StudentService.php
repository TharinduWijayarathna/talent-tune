<?php

namespace App\Services\Application;

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use App\Services\Ai\RubricService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

        $charts = $this->getStudentChartData($user);

        return [
            'stats' => $stats,
            'upcomingVivas' => $upcomingVivas,
            'charts' => $charts,
        ];
    }

    /**
     * Chart data for student dashboard: completed vs upcoming, completions over time.
     */
    private function getStudentChartData(User $user): array
    {
        $completedCount = VivaStudentSubmission::where('student_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $upcomingCount = 0;
        if ($user->batch) {
            $upcomingCount = Viva::where('institution_id', $user->institution_id)
                ->where('batch', $user->batch)
                ->where('status', 'upcoming')
                ->count();
        }

        $sessionsBreakdown = [
            'labels' => ['Completed', 'Upcoming'],
            'series' => [$completedCount, $upcomingCount],
        ];

        $days = 30;
        $start = now()->subDays($days)->startOfDay();
        $rows = VivaStudentSubmission::where('student_id', $user->id)
            ->where('status', 'completed')
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
        $completionsOverTime = ['labels' => $labels, 'series' => [$data]];

        return [
            'sessionsBreakdown' => $sessionsBreakdown,
            'completionsOverTime' => $completionsOverTime,
        ];
    }

    /**
     * Get viva sessions for the student (only vivas in their batch).
     */
    public function getVivaSessions(Institution $institution, User $user): array
    {
        if (! $user->batch) {
            return [];
        }

        Viva::closeOverdueVivas();

        $vivas = Viva::where('institution_id', $institution->id)
            ->where('batch', $user->batch)
            ->with('lecturer')
            ->orderBy('scheduled_at', 'desc')
            ->get();

        $allSubmissions = VivaStudentSubmission::where('student_id', $user->id)
            ->whereIn('viva_id', $vivas->pluck('id'))
            ->get();
        $submissionsByViva = $allSubmissions->groupBy('viva_id');

        // Compare in UTC: stored scheduled_at is in UTC
        $nowUtc = now()->utc();

        return $vivas->map(function (Viva $viva) use ($submissionsByViva, $nowUtc) {
            $subs = $submissionsByViva->get($viva->id, collect());
            $completedSub = $subs->where('status', 'completed')->sortByDesc('id')->first();
            $marks = $completedSub ? $completedSub->total_score : null;
            $grade = $completedSub ? $completedSub->grade : null;
            $hasCompleted = $subs->contains('status', 'completed');
            $hasPendingLate = $viva->status === 'completed' && $subs->contains(fn ($s) => $s->allowed_after_close && in_array($s->status, ['pending', 'in_progress'], true));

            $rawScheduled = $viva->getRawOriginal('scheduled_at');
            $scheduledAtUtc = $rawScheduled ? Carbon::parse($rawScheduled, 'UTC') : $viva->scheduled_at->copy()->utc();
            $rawDue = $viva->getRawOriginal('due_at');
            $dueAtUtc = $rawDue ? Carbon::parse($rawDue, 'UTC') : ($viva->due_at ? $viva->due_at->copy()->utc() : null);
            $closedByDue = $viva->status === 'completed' && $dueAtUtc && $dueAtUtc->lte($nowUtc);

            $scheduledReached = $scheduledAtUtc->lte($nowUtc);
            $notClosed = $viva->status !== 'completed';
            $can_attend = (($scheduledReached && $notClosed) && ! $hasCompleted) || $hasPendingLate;

            return [
                'id' => $viva->id,
                'title' => $viva->title,
                'description' => $viva->description,
                'date' => $viva->scheduled_at->format('Y-m-d'),
                'time' => $viva->scheduled_at->format('g:i A'),
                'scheduled_at' => $scheduledAtUtc->toIso8601String(),
                'due_at' => $dueAtUtc?->toIso8601String(),
                'closed_by_due' => $closedByDue,
                'lecturer' => $viva->lecturer->name,
                'status' => $viva->status,
                'batch' => $viva->batch,
                'materials' => $viva->lecture_materials,
                'marks' => $marks,
                'grade' => $grade,
                'can_attend' => $can_attend,
            ];
        })->all();
    }

    /**
     * Get viva for attend. Student may attend only on/after scheduled date and until lecturer closes the viva or due date passes.
     * When viva is closed, a student can attend only if the lecturer added them for one-time (late) participation (allowed_after_close).
     */
    public function getVivaForAttend(Institution $institution, User $user, int $id): array
    {
        Viva::closeOverdueVivas();

        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $id)
            ->where('batch', $user->batch)
            ->with('lecturer')
            ->firstOrFail();

        if ($viva->status === 'completed') {
            $submission = VivaStudentSubmission::where('viva_id', $viva->id)
                ->where('student_id', $user->id)
                ->where('allowed_after_close', true)
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderByDesc('id')
                ->first();

            if (! $submission) {
                $anySubmission = VivaStudentSubmission::where('viva_id', $viva->id)
                    ->where('student_id', $user->id)
                    ->first();
                if ($anySubmission && $anySubmission->allowed_after_close && $anySubmission->status === 'completed') {
                    abort(403, 'You have already completed your one-time participation for this viva. Only the lecturer can add you again for another attempt.');
                }
                $rawDue = $viva->getRawOriginal('due_at');
                $dueAtUtc = $rawDue ? Carbon::parse($rawDue, 'UTC') : ($viva->due_at ? $viva->due_at->copy()->utc() : null);
                $closedByDue = $dueAtUtc && $dueAtUtc->lte(now()->utc());
                $message = $closedByDue
                    ? 'This viva has closed (due date passed). You can no longer attend unless the lecturer adds you for one-time participation.'
                    : 'This viva has been closed by the lecturer. You can no longer attend unless the lecturer adds you for one-time participation.';
                abort(403, $message);
            }

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

        $rawScheduled = $viva->getRawOriginal('scheduled_at');
        $scheduledAtUtc = $rawScheduled ? Carbon::parse($rawScheduled, 'UTC') : $viva->scheduled_at->copy()->utc();
        if ($scheduledAtUtc->isFuture()) {
            abort(403, 'This viva opens on '.$scheduledAtUtc->format('M j, Y \a\t g:i A').' UTC. You cannot attend before the scheduled date and time.');
        }

        // Use the regular (non-late) submission for the open window; only one per student per viva from this window
        $submission = VivaStudentSubmission::where('viva_id', $viva->id)
            ->where('student_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('allowed_after_close')->orWhere('allowed_after_close', false);
            })
            ->first();

        if ($submission && $submission->status === 'completed') {
            abort(403, 'You have already completed this viva. You can only attend once.');
        }

        if (! $submission) {
            $submission = VivaStudentSubmission::create([
                'viva_id' => $viva->id,
                'student_id' => $user->id,
                'status' => 'pending',
                'allowed_after_close' => false,
            ]);
        }

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

    /**
     * Upload a voice recording for one answer. Stored under vivas/voice-recordings/{submission_id}/{index}.{ext}.
     */
    public function uploadVivaVoiceRecording(Institution $institution, User $user, int $vivaId, int $submissionId, int $questionIndex, UploadedFile $audio): array
    {
        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $vivaId)
            ->where('batch', $user->batch)
            ->firstOrFail();

        $submission = VivaStudentSubmission::where('viva_id', $viva->id)
            ->where('student_id', $user->id)
            ->where('id', $submissionId)
            ->firstOrFail();

        if ($questionIndex < 0 || $questionIndex > 9) {
            abort(422, 'Invalid question index.');
        }

        $allowedExtensions = ['webm', 'mp3', 'mp4', 'm4a', 'ogg', 'wav'];
        $ext = strtolower((string) $audio->getClientOriginalExtension());
        if (! in_array($ext, $allowedExtensions, true)) {
            $ext = 'webm';
        }

        $directory = 'vivas/voice-recordings/'.$submission->id;
        Storage::disk('private')->makeDirectory($directory);

        $path = $audio->storeAs(
            $directory,
            $questionIndex.'.'.$ext,
            'private'
        );

        return [
            'success' => true,
            'voice_path' => $path,
        ];
    }

    /**
     * Get the student's own submission for a viva for the "View my answers" page.
     * Returns the latest completed submission (or latest submission with answers).
     * Only vivas in the student's batch; answers include question, answer, voice_path, and feedback (no score_1_10/correctPoints/improvements).
     */
    public function getMySubmissionForViva(Institution $institution, User $user, int $vivaId): ?array
    {
        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $vivaId)
            ->where('batch', $user->batch)
            ->with('lecturer')
            ->firstOrFail();

        $submission = VivaStudentSubmission::where('viva_id', $viva->id)
            ->where('student_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('id')
            ->first();

        if (! $submission) {
            return null;
        }

        $answers = $submission->answers ?? [];
        $answersForStudent = array_map(function ($item) {
            return [
                'question' => $item['question'] ?? '',
                'answer' => $item['answer'] ?? '',
                'voice_path' => $item['voice_path'] ?? null,
                'feedback' => $item['feedback'] ?? null,
            ];
        }, $answers);

        return [
            'viva' => [
                'id' => $viva->id,
                'title' => $viva->title,
                'lecturer' => $viva->lecturer->name,
            ],
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'total_score' => $submission->total_score,
                'grade' => $submission->grade,
                'feedback' => $submission->feedback,
                'answers' => $answersForStudent,
                'document_path' => $submission->document_path ? true : false,
            ],
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
            $submission->total_score = (int) round($rubric['total_score'] ?? $rubric['score']);
            $submission->grade = isset($rubric['grade']) ? (string) $rubric['grade'] : null;
            $submission->feedback = null;
        } else {
            $submission->total_score = (int) round(array_sum($scores) / 5 * 10);
            $submission->grade = null;
            $submission->feedback = 'Rubric service unavailable; score is average of question scores.';
        }

        $submission->save();

        return [
            'success' => true,
            'rubric_score' => $submission->total_score,
            'grade' => $submission->grade,
            'rubric_from_service' => $rubric['success'],
        ];
    }
}
