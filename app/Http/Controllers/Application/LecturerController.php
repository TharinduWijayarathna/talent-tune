<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\Application\LecturerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LecturerController extends Controller
{
    public function __construct(
        protected LecturerService $lecturerService
    ) {}

    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (!$institution) {
            abort(403, 'Institution context required.');
        }
        return $institution;
    }

    protected function authorizeLecturer(Request $request): void
    {
        $user = $request->user();
        $institution = $this->institution($request);

        if (!$user) {
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
            'instructions' => ['nullable', 'string'],
            'lecture_materials' => ['nullable', 'array'],
            'lecture_materials.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
        ]);

        $files = $request->file('lecture_materials', []);

        $this->lecturerService->createViva($institution, $user, $validated, $files);

        return redirect()->route('lecturer.dashboard')->with('status', 'Viva session created successfully.');
    }

    public function showViva(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeLecturer($request);
        $user = $request->user();

        $viva = $this->lecturerService->getVivaForShow($institution, $user, $id);

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
