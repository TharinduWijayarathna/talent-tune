<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Get the current institution from request (set by SetInstitutionContext middleware).
     */
    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (!$institution) {
            abort(403, 'Institution context required.');
        }
        return $institution;
    }

    /**
     * Ensure the user is a student in the current institution.
     */
    protected function authorizeStudent(Request $request): void
    {
        $user = $request->user();
        $institution = $this->institution($request);

        if (!$user) {
            abort(403);
        }

        // Admin can access any institution's data
        if ($user->role === 'admin') {
            return;
        }

        // Student must belong to the current institution
        if ($user->role !== 'student' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    /**
     * Student dashboard - shows stats and upcoming vivas for the student's institution.
     */
    public function dashboard(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        // TODO: When Viva model exists, query vivas scoped to institution
        // For now, return empty data structure
        $stats = [
            'upcomingVivas' => 0,
            'completedVivas' => 0,
            'averageMarks' => 0,
            'totalSessions' => 0,
        ];

        $upcomingVivas = [];
        // TODO: When Viva model exists:
        // $upcomingVivas = Viva::where('institution_id', $institution->id)
        //     ->where('student_id', $user->id) // or where students are assigned
        //     ->where('status', 'upcoming')
        //     ->orderBy('scheduled_at')
        //     ->get();

        return Inertia::render('student/Dashboard', [
            'stats' => $stats,
            'upcomingVivas' => $upcomingVivas,
        ]);
    }

    /**
     * List all viva sessions for the student's institution.
     */
    public function vivas(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        // TODO: When Viva model exists, query vivas scoped to institution
        $vivaSessions = [];
        // TODO: When Viva model exists:
        // $vivaSessions = Viva::where('institution_id', $institution->id)
        //     ->where('student_id', $user->id) // or where students are assigned
        //     ->orderBy('scheduled_at', 'desc')
        //     ->get()
        //     ->map(fn ($viva) => [
        //         'id' => $viva->id,
        //         'title' => $viva->title,
        //         'description' => $viva->description,
        //         'date' => $viva->scheduled_at->format('Y-m-d'),
        //         'time' => $viva->scheduled_at->format('h:i A'),
        //         'lecturer' => $viva->lecturer->name,
        //         'status' => $viva->status,
        //         'batch' => $viva->batch,
        //         'marks' => $viva->marks,
        //     ]);

        return Inertia::render('student/VivaSessions', [
            'vivaSessions' => $vivaSessions,
        ]);
    }

    /**
     * Show viva attend page (only for vivas in student's institution).
     */
    public function attendViva(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $id)
            ->where('batch', $user->batch) // Student must be in the same batch
            ->with('lecturer')
            ->firstOrFail();

        // Get or create student submission
        $submission = VivaStudentSubmission::firstOrCreate(
            [
                'viva_id' => $viva->id,
                'student_id' => $user->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        return Inertia::render('student/VivaAttend', [
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
        ]);
    }

    /**
     * Upload student document for viva
     */
    public function uploadVivaDocument(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        $viva = Viva::where('institution_id', $institution->id)
            ->where('id', $id)
            ->where('batch', $user->batch)
            ->firstOrFail();

        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // Max 10MB
        ]);

        // Get or create submission
        $submission = VivaStudentSubmission::firstOrCreate(
            [
                'viva_id' => $viva->id,
                'student_id' => $user->id,
            ],
            [
                'status' => 'pending',
            ]
        );

        // Delete old document if exists
        if ($submission->document_path && Storage::exists($submission->document_path)) {
            Storage::delete($submission->document_path);
        }

        // Store new document
        $path = $validated['document']->store('vivas/student-documents', 'private');
        $submission->document_path = $path;
        $submission->status = 'in_progress';
        $submission->save();

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'document_path' => $path,
        ]);
    }

    /**
     * Show student marks (only for vivas in student's institution).
     */
    public function marks(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeStudent($request);
        $user = $request->user();

        // TODO: When Viva model exists, query marks scoped to institution
        $marks = [];
        // TODO: When Viva model exists:
        // $marks = VivaResult::whereHas('viva', function ($query) use ($institution) {
        //         $query->where('institution_id', $institution->id);
        //     })
        //     ->where('student_id', $user->id)
        //     ->with('viva')
        //     ->orderBy('created_at', 'desc')
        //     ->get()
        //     ->map(fn ($result) => [
        //         'id' => $result->id,
        //         'vivaTitle' => $result->viva->title,
        //         'lecturer' => $result->viva->lecturer->name,
        //         'date' => $result->created_at->format('Y-m-d'),
        //         'marks' => $result->marks,
        //         'maxMarks' => $result->max_marks,
        //         'grade' => $result->grade,
        //         'feedback' => $result->feedback,
        //     ]);

        return Inertia::render('student/Marks', [
            'marks' => $marks,
        ]);
    }
}
