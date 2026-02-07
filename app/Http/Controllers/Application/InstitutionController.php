<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\Application\InstitutionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionController extends Controller
{
    public function __construct(
        protected InstitutionService $institutionService
    ) {}

    /**
     * Show the institution registration form.
     */
    public function create()
    {
        return Inertia::render('home/RegisterInstitution');
    }

    /**
     * Store a newly created institution.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:institutions,name',
            'email' => 'required|email|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $institution = $this->institutionService->create($validated);

        return redirect()->route('register-institution.success', [
            'id' => $institution->id,
        ])->with('success', 'Your institution registration has been submitted. Our admin team will review and activate your account soon.');
    }

    /**
     * Show registration success page.
     */
    public function success(Request $request, $id)
    {
        $institution = Institution::findOrFail($id);

        return Inertia::render('home/RegisterInstitutionSuccess', [
            'institution' => $institution->only(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Display a listing of institutions (admin only).
     */
    public function index()
    {
        $institutions = $this->institutionService->getListForAdmin();

        return Inertia::render('admin/Institutions', [
            'institutions' => $institutions,
        ]);
    }

    /**
     * Update institution status (activate/deactivate).
     */
    public function updateStatus(Request $request, Institution $institution)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $this->institutionService->updateStatus($institution, $request->boolean('is_active'), $request);

        return back()->with('success', $request->is_active
            ? 'Institution activated successfully. Admin user created and credentials sent via email.'
            : 'Institution deactivated successfully.');
    }

    /**
     * Delete an institution.
     */
    public function destroy(Institution $institution)
    {
        $institution->delete();

        return back()->with('success', 'Institution deleted successfully.');
    }
}
