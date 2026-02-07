<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BatchController extends Controller
{
    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (!$institution) {
            abort(403, 'Institution context required.');
        }
        return $institution;
    }

    protected function authorizeInstitution(?User $user, ?Institution $institution): void
    {
        if (!$user) {
            abort(403);
        }
        if ($user->role === 'admin') {
            return;
        }
        if ($user->role !== 'institution' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    /**
     * List batches for the current institution.
     */
    public function index(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $batches = Batch::where('institution_id', $institution->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Batch $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'students_count' => User::forInstitution($institution->id)
                    ->where('role', 'student')
                    ->where('batch', $b->name)
                    ->count(),
                'created_at' => $b->created_at->toISOString(),
            ]);

        return Inertia::render('institution/Batches', [
            'batches' => $batches,
        ]);
    }

    /**
     * Store a new batch.
     */
    public function store(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('batches')->where('institution_id', $institution->id),
            ],
        ]);

        Batch::create([
            'institution_id' => $institution->id,
            'name' => $validated['name'],
        ]);

        return redirect()->route('institution.batches')->with('status', 'Batch created successfully.');
    }

    /**
     * Delete a batch (name remains on existing students/vivas; only the predefined batch is removed).
     */
    public function destroy(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $batch = Batch::where('institution_id', $institution->id)->findOrFail($id);
        $batch->delete();

        return redirect()->route('institution.batches')->with('status', 'Batch removed.');
    }
}
