<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\VivaStudentSubmission;
use App\Services\Application\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}

    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (! $institution) {
            abort(403, 'Institution context required.');
        }

        return $institution;
    }

    protected function authorizeStudent(Request $request): void
    {
        $user = $request->user();
        $institution = $this->institution($request);

        if (! $user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role !== 'student' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    public function dashboard(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $data = $this->studentService->getDashboardData($institution, $user);

        return Inertia::render('student/Dashboard', [
            'stats' => $data['stats'],
            'upcomingVivas' => $data['upcomingVivas'],
            'charts' => $data['charts'],
        ]);
    }

    public function vivas(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $vivaSessions = $this->studentService->getVivaSessions($institution, $user);

        return Inertia::render('student/VivaSessions', [
            'vivaSessions' => $vivaSessions,
        ]);
    }

    public function attendViva(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $data = $this->studentService->getVivaForAttend($institution, $user, $id);

        return Inertia::render('student/VivaAttend', [
            'viva' => $data['viva'],
            'submission' => $data['submission'],
        ]);
    }

    /**
     * Show the student's own completed submission (answers, result, feedback) for a viva.
     */
    public function showVivaSubmission(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $data = $this->studentService->getMySubmissionForViva($institution, $user, $id);
        if (! $data) {
            abort(404, 'You have not completed this viva yet, or your submission is not available.');
        }

        return Inertia::render('student/VivaSubmission', [
            'viva' => $data['viva'],
            'submission' => $data['submission'],
        ]);
    }

    /**
     * Stream the student's own voice recording for one answer. Student must own the submission.
     */
    public function streamSubmissionVoice(Request $request, int $submissionId, int $index): BinaryFileResponse
    {
        $this->authorizeStudent($request);
        $user = $request->user();

        $submission = VivaStudentSubmission::where('id', $submissionId)
            ->where('student_id', $user->id)
            ->firstOrFail();

        $answers = $submission->answers ?? [];
        if ($index < 0 || $index >= count($answers)) {
            abort(404, 'Voice recording not found.');
        }
        $voicePath = $answers[$index]['voice_path'] ?? null;
        if (! $voicePath || ! Storage::disk('private')->exists($voicePath)) {
            abort(404, 'Voice recording not found.');
        }

        $mime = match (strtolower(pathinfo($voicePath, PATHINFO_EXTENSION))) {
            'webm' => 'audio/webm',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
            'm4a', 'mp4' => 'audio/mp4',
            default => 'audio/webm',
        };

        return response()->file(Storage::disk('private')->path($voicePath), [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="answer-'.($index + 1).'.webm"',
        ]);
    }

    /**
     * Stream or download the student's own uploaded document for a submission.
     */
    public function streamSubmissionDocument(Request $request, int $submissionId): BinaryFileResponse
    {
        $this->authorizeStudent($request);
        $user = $request->user();

        $submission = VivaStudentSubmission::where('id', $submissionId)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if (! $submission->document_path || ! Storage::disk('private')->exists($submission->document_path)) {
            abort(404, 'Document not found.');
        }

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response()->file(Storage::disk('private')->path($submission->document_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="my-viva-document.pdf"',
        ]);
    }

    public function uploadVivaDocument(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $result = $this->studentService->uploadVivaDocument(
            $institution,
            $user,
            $id,
            $validated['document']
        );

        return response()->json($result);
    }

    public function uploadVivaVoice(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $validated = $request->validate([
            'submission_id' => ['required', 'integer', 'exists:viva_student_submissions,id'],
            'question_index' => ['required', 'integer', 'min:0', 'max:9'],
            'audio' => ['required', 'file', 'mimes:webm,mp3,mp4,m4a,ogg,wav', 'max:20480'],
        ]);

        try {
            $result = $this->studentService->uploadVivaVoiceRecording(
                $institution,
                $user,
                $id,
                $validated['submission_id'],
                $validated['question_index'],
                $validated['audio']
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => 'Failed to save voice recording.',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function completeVivaSubmission(Request $request)
    {
        $this->authorizeStudent($request);
        $user = $request->user();

        $validated = $request->validate([
            'submission_id' => ['required', 'integer', 'exists:viva_student_submissions,id'],
            'answers' => ['required', 'array', 'size:5'],
            'answers.*.question' => ['required', 'string'],
            'answers.*.answer' => ['required', 'string'],
            'answers.*.score_1_10' => ['required', 'integer', 'min:1', 'max:10'],
            'answers.*.feedback' => ['nullable', 'string'],
            'answers.*.correctPoints' => ['nullable', 'array'],
            'answers.*.improvements' => ['nullable', 'array'],
            'answers.*.voice_path' => ['nullable', 'string'],
        ]);

        $result = $this->studentService->completeVivaSubmission($user, $validated);

        return response()->json($result);
    }
}
