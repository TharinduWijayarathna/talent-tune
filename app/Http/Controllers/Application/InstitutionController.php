<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\Application\InstitutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
     * Show the form for editing an institution (admin only).
     */
    public function edit(Institution $institution)
    {
        return Inertia::render('admin/EditInstitution', [
            'institution' => [
                'id' => $institution->id,
                'name' => $institution->name,
                'slug' => $institution->slug,
                'email' => $institution->email ?? ($institution->settings['email'] ?? null),
                'contact_person' => $institution->contact_person ?? ($institution->settings['contact_person'] ?? null),
                'phone' => $institution->phone ?? ($institution->settings['phone'] ?? null),
                'address' => $institution->address ?? ($institution->settings['address'] ?? null),
                'primary_color' => $institution->primary_color ?? '#3b82f6',
                'is_active' => $institution->is_active,
                'subscription_status' => $institution->subscription_status,
            ],
        ]);
    }

    /**
     * Update the specified institution (admin only).
     */
    public function update(Request $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('institutions', 'name')->ignore($institution->id)],
            'email' => ['required', 'string', 'email', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['required', 'boolean'],
        ]);

        $this->institutionService->update($institution, $validated);

        return redirect()->route('admin.institutions')->with('success', 'Institution updated successfully.');
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
