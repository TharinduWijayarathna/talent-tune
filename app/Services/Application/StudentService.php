<?php

namespace App\Services\Application;

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use App\Services\Ai\RubricService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    public function __construct(
        protected RubricService $rubricService
    ) {}

    public function getDashboardData(Institution $institution, User $user): array
    {
        $stats = [
            'upcomingVivas' => 0,
            'completedVivas' => 0,
            'averageMarks' => 0,
            'totalSessions' => 0,
        ];
        $upcomingVivas = [];
        return [
            'stats' => $stats,
            'upcomingVivas' => $upcomingVivas,
        ];
    }

    public function getVivaSessions(Institution $institution, User $user): array
    {
        return [];
    }

    public function getVivaForAttend(Institution $institution, User $user, int $id): array
    {
        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $id)
            ->where('batch', $user->batch)
            ->with('lecturer')
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

    public function getMarks(Institution $institution, User $user): array
    {
        return [];
    }
}
