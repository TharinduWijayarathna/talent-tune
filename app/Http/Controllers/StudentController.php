<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
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

        // TODO: When Viva model exists:
        // $viva = Viva::where('institution_id', $institution->id)
        //     ->where('id', $id)
        //     ->where('student_id', $user->id) // or check if student is assigned
        //     ->firstOrFail();

        return Inertia::render('student/VivaAttend', [
            'vivaId' => $id,
            // 'viva' => $viva, // When model exists
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
