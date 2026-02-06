<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Services\GeminiFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LecturerController extends Controller
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
     * Ensure the user is a lecturer in the current institution.
     */
    protected function authorizeLecturer(Request $request): void
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

        // Lecturer must belong to the current institution
        if ($user->role !== 'lecturer' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    /**
     * Lecturer dashboard - shows stats and sessions for the lecturer's institution.
     */
    public function dashboard(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

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
            });

        return Inertia::render('lecturer/Dashboard', [
            'stats' => $stats,
            'recentSessions' => $recentSessions,
        ]);
    }

    /**
     * List all viva sessions created by the lecturer (in their institution).
     */
    public function vivas(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $vivas = Viva::where('institution_id', $institution->id)
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
            });

        return Inertia::render('lecturer/Vivas', [
            'vivas' => $vivas,
        ]);
    }

    /**
     * Show create viva form.
     */
    public function createViva(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);

        // Get batches from students in this institution
        $batches = User::forInstitution($institution->id)
            ->where('role', 'student')
            ->whereNotNull('batch')
            ->distinct()
            ->pluck('batch')
            ->filter()
            ->values()
            ->toArray();

        return Inertia::render('lecturer/CreateViva', [
            'batches' => $batches,
            'institutionId' => $institution->id,
        ]);
    }

    /**
     * Store a new viva session (automatically scoped to lecturer's institution).
     */
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
            'instructions' => ['nullable', 'string'],
            'lecture_materials' => ['nullable', 'array'],
            'lecture_materials.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'], // Max 10MB per file
        ]);

        // Store uploaded files
        $storedFiles = [];
        if ($request->hasFile('lecture_materials')) {
            foreach ($request->file('lecture_materials') as $file) {
                $path = $file->store('vivas/lecture-materials', 'private');
                $storedFiles[] = $path;
            }
        }

        // Process files with Gemini to generate background and base prompt
        $geminiService = new GeminiFileService();
        $processedData = $geminiService->processLectureMaterials(
            $storedFiles,
            $validated['title'],
            $validated['description'] ?? null
        );

        // Create viva record
        $viva = Viva::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'batch' => $validated['batch'],
            'scheduled_at' => Carbon::parse($validated['date'] . ' ' . $validated['time']),
            'instructions' => $validated['instructions'] ?? null,
            'lecture_materials' => $storedFiles,
            'viva_background' => $processedData['background'],
            'base_prompt' => $processedData['base_prompt'],
            'institution_id' => $institution->id,
            'lecturer_id' => $user->id,
            'status' => 'upcoming',
        ]);

        return redirect()->route('lecturer.dashboard')->with('status', 'Viva session created successfully.');
    }

    /**
     * Show viva details (only for vivas created by lecturer in their institution).
     */
    public function showViva(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $viva = Viva::where('institution_id', $institution->id)
            ->where('lecturer_id', $user->id)
            ->findOrFail($id);

        return Inertia::render('lecturer/ShowViva', [
            'viva' => [
                'id' => $viva->id,
                'title' => $viva->title,
                'description' => $viva->description,
                'batch' => $viva->batch,
                'scheduled_at' => $viva->scheduled_at->format('Y-m-d H:i'),
                'instructions' => $viva->instructions,
                'status' => $viva->status,
            ],
        ]);
    }
}
