<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\Application\StudentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}

    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (!$institution) {
            abort(403, 'Institution context required.');
        }
        return $institution;
    }

    protected function authorizeStudent(Request $request): void
    {
        $user = $request->user();
        $institution = $this->institution($request);

        if (!$user) {
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

    public function uploadVivaDocument(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $result = $this->studentService->uploadVivaDocument(
            $institution,
            $user,
            $id,
            $validated['document']
        );

        return response()->json($result);
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
        ]);

        $result = $this->studentService->completeVivaSubmission($user, $validated);

        return response()->json($result);
    }

    public function marks(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $marks = $this->studentService->getMarks($institution, $user);

        return Inertia::render('student/Marks', [
            'marks' => $marks,
        ]);
    }
}
