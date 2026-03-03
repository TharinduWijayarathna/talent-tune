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
        ]);

        $institution = $this->institutionService->create($validated, $request);

        return redirect()->route('register-institution.success', [
            'id' => $institution->id,
        ])->with('success', 'Your institution has been registered. Check the contact email for your login credentials and 14-day trial details.');
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
     * Show institution detail (admin only).
     */
    public function show(Institution $institution)
    {
        return Inertia::render('admin/InstitutionDetail', [
            'institution' => [
                'id' => $institution->id,
                'name' => $institution->name,
                'slug' => $institution->slug,
                'email' => $institution->email ?? ($institution->settings['email'] ?? null),
                'contact_person' => $institution->contact_person ?? ($institution->settings['contact_person'] ?? null),
                'phone' => $institution->phone ?? ($institution->settings['phone'] ?? null),
                'address' => $institution->address ?? ($institution->settings['address'] ?? null),
                'is_active' => $institution->is_active,
                'subscription_status' => $institution->subscription_status,
                'trial_ends_at' => $institution->trial_ends_at?->toISOString(),
                'created_at' => $institution->created_at->toISOString(),
            ],
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
                'is_active' => $institution->is_active,
                'subscription_status' => $institution->subscription_status,
                'trial_ends_at' => $institution->trial_ends_at?->toISOString(),
            ],
        ]);
    }

    /**
     * End institution trial immediately (admin only).
     */
    public function endTrial(Institution $institution)
    {
        $this->institutionService->endTrial($institution);

        return back()->with('success', 'Trial ended. Institution must complete payment to continue access.');
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
            'is_active' => ['required', 'boolean'],
        ]);

        $this->institutionService->update($institution, $validated);

        return redirect()->route('admin.institutions.show', $institution)->with('success', 'Institution updated successfully.');
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
