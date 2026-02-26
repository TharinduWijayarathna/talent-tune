<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\VivaStudentSubmission;
use App\Services\Application\LecturerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LecturerController extends Controller
{
    public function __construct(
        protected LecturerService $lecturerService
    ) {}

    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (! $institution) {
            abort(403, 'Institution context required.');
        }

        return $institution;
    }

    protected function authorizeLecturer(Request $request): void
    {
        $user = $request->user();
        $institution = $this->institution($request);

        if (! $user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role !== 'lecturer' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    public function dashboard(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $data = $this->lecturerService->getDashboardData($institution, $user);

        return Inertia::render('lecturer/Dashboard', [
            'stats' => $data['stats'],
            'recentSessions' => $data['recentSessions'],
        ]);
    }

    public function vivas(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $vivas = $this->lecturerService->getVivas($institution, $user);

        return Inertia::render('lecturer/Vivas', [
            'vivas' => $vivas,
        ]);
    }

    public function createViva(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);

        $batches = $this->lecturerService->getBatchesForInstitution($institution);

        return Inertia::render('lecturer/CreateViva', [
            'batches' => $batches,
            'institutionId' => $institution->id,
        ]);
    }

    public function storeViva(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'batch' => ['required', 'string'],
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'instructions' => ['nullable', 'string'],
        ]);

        $this->lecturerService->createViva($institution, $user, $validated);

        return redirect()->route('lecturer.dashboard')->with('status', 'Viva session created successfully.');
    }

    public function showViva(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $viva = $this->lecturerService->getVivaForShow($institution, $user, $id);
        $submissions = $this->lecturerService->getVivaSubmissionsForShow($institution, $user, $id);

        $rawScheduled = $viva->getRawOriginal('scheduled_at');
        $scheduledAtUtc = $rawScheduled
            ? Carbon::parse($rawScheduled, 'UTC')
            : $viva->scheduled_at->copy()->utc();

        return Inertia::render('lecturer/ShowViva', [
            'viva' => [
                'id' => $viva->id,
                'title' => $viva->title,
                'description' => $viva->description,
                'batch' => $viva->batch,
                'scheduled_at' => $scheduledAtUtc->toIso8601String(),
                'instructions' => $viva->instructions,
                'status' => $viva->status,
            ],
            'submissions' => $submissions,
        ]);
    }

    public function closeViva(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $this->lecturerService->closeViva($institution, $user, $id);

        return redirect()->route('lecturer.vivas.show', ['id' => $id])->with('status', 'Viva has been closed. Students can no longer attend.');
    }

    /**
     * Get students in the viva's batch who can be added for one-time participation (viva must be closed).
     */
    public function studentsForLateParticipation(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $students = $this->lecturerService->getStudentsForLateParticipation($institution, $user, $id);

        return response()->json(['students' => $students]);
    }

    /**
     * Add a student for one-time participation after the viva is closed.
     */
    public function addLateStudent(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $this->lecturerService->addStudentForLateParticipation($institution, $user, $id, (int) $validated['student_id']);

        return redirect()->route('lecturer.vivas.show', ['id' => $id])->with('status', 'Student added for one-time participation. They can now attend and complete the viva once.');
    }

    /**
     * Stream a submission's uploaded document. Lecturer must own the viva.
     */
    public function streamSubmissionDocument(Request $request, int $submissionId): BinaryFileResponse
    {
        $this->authorizeLecturer($request);
        $user = $request->user();

        $submission = VivaStudentSubmission::with('viva')->findOrFail($submissionId);
        if ($submission->viva->lecturer_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'You do not have access to this submission.');
        }
        if (! $submission->document_path || ! Storage::disk('private')->exists($submission->document_path)) {
            abort(404, 'Document not found.');
        }

        return response()->file(Storage::disk('private')->path($submission->document_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="viva-document.pdf"',
        ]);
    }

    /**
     * Stream a submission's voice recording for one answer. Lecturer must own the viva.
     */
    public function streamSubmissionVoice(Request $request, int $submissionId, int $index): BinaryFileResponse
    {
        $this->authorizeLecturer($request);
        $user = $request->user();

        $submission = VivaStudentSubmission::with('viva')->findOrFail($submissionId);
        if ($submission->viva->lecturer_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'You do not have access to this submission.');
        }

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
}
