<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAdminService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminAdminController extends Controller
{
    public function __construct(
        protected AdminAdminService $adminAdminService
    ) {}

    /**
     * List all TalentTune admins.
     */
    public function index()
    {
        $admins = $this->adminAdminService->getAdmins();

        return Inertia::render('admin/TalentTuneAdmins', [
            'admins' => $admins,
        ]);
    }

    /**
     * Show the form to add a new TalentTune admin.
     */
    public function create()
    {
        return Inertia::render('admin/AddTalentTuneAdmin');
    }

    /**
     * Store a new TalentTune admin and send credentials.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->adminAdminService->createAdmin($validated);

        return redirect()
            ->route('admin.talenttune-admins')
            ->with('success', 'TalentTune admin added successfully. Credentials have been sent to their email.');
    }
}
