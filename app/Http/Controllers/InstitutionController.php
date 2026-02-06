<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use App\Notifications\InstitutionActivated;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InstitutionController extends Controller
{
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

        // Generate slug from name
        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;

        // Ensure unique slug
        while (Institution::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Create institution (inactive by default, awaiting admin approval)
        $institution = Institution::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'email' => $validated['email'],
            'contact_person' => $validated['contact_person'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'primary_color' => $validated['primary_color'] ?? '#3b82f6',
            'settings' => [
                'email' => $validated['email'],
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ],
            'is_active' => false, // Requires admin approval
        ]);

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
        $institutions = Institution::latest()->get()->map(function ($institution) {
            return [
                'id' => $institution->id,
                'name' => $institution->name,
                'slug' => $institution->slug,
                'email' => $institution->email ?? ($institution->settings['email'] ?? null),
                'contact_person' => $institution->contact_person ?? ($institution->settings['contact_person'] ?? null),
                'phone' => $institution->phone ?? ($institution->settings['phone'] ?? null),
                'address' => $institution->address ?? ($institution->settings['address'] ?? null),
                'is_active' => $institution->is_active,
                'created_at' => $institution->created_at->toISOString(),
            ];
        });

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

        $wasInactive = !$institution->is_active;
        $isActivating = $wasInactive && $request->is_active;

        $institution->update([
            'is_active' => $request->is_active,
        ]);

        // If activating, create admin user and send email
        if ($isActivating) {
            $this->activateInstitution($institution, $request);
        }

        return back()->with('success', $request->is_active 
            ? 'Institution activated successfully. Admin user created and credentials sent via email.' 
            : 'Institution deactivated successfully.');
    }

    /**
     * Activate institution: create admin user and send credentials.
     */
    protected function activateInstitution(Institution $institution, Request $request): void
    {
        // Check if admin user already exists
        $existingAdmin = User::where('institution_id', $institution->id)
            ->where('role', 'institution')
            ->first();

        if ($existingAdmin) {
            // Admin already exists, just send activation email
            $password = Str::random(12);
            $existingAdmin->update([
                'password' => Hash::make($password),
            ]);

            $this->sendActivationEmail($institution, $existingAdmin->email, $password, $request);
            return;
        }

        // Generate email and password for admin user
        $adminEmail = $institution->email ?? ($institution->settings['email'] ?? null);
        
        if (!$adminEmail) {
            Log::warning("Cannot activate institution {$institution->id}: No email address found");
            return;
        }

        // Check if user with this email already exists
        $existingUser = User::where('email', $adminEmail)->first();
        
        if ($existingUser) {
            // Update existing user to be admin for this institution
            $password = Str::random(12);
            $existingUser->update([
                'institution_id' => $institution->id,
                'role' => 'institution',
                'password' => Hash::make($password),
            ]);

            $this->sendActivationEmail($institution, $adminEmail, $password, $request);
            return;
        }

        // Create new admin user
        $password = Str::random(12);
        $adminUser = User::create([
            'name' => $institution->contact_person ?? $institution->name,
            'email' => $adminEmail,
            'password' => Hash::make($password),
            'role' => 'institution',
            'institution_id' => $institution->id,
            'department' => 'Administration',
            'email_verified_at' => now(),
        ]);

        // Send activation email
        $this->sendActivationEmail($institution, $adminEmail, $password, $request);
    }

    /**
     * Send activation email with credentials.
     */
    protected function sendActivationEmail(Institution $institution, string $email, string $password, Request $request): void
    {
        // Get base domain
        $host = $request->getHost();
        $parts = explode('.', $host);
        $baseDomain = str_ends_with($host, '.test') 
            ? (count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host)
            : (count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host);

        // Send notification directly to email address
        Notification::route('mail', $email)
            ->notify(new InstitutionActivated($institution, $email, $password, $baseDomain));
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
