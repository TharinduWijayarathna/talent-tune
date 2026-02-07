<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function __construct(
        protected AdminUserService $adminUserService
    ) {}

    /**
     * Display a listing of all users (admin only).
     */
    public function index(Request $request)
    {
        $data = $this->adminUserService->getUsersWithFilters($request);

        return Inertia::render('admin/Users', [
            'users' => $data['users'],
            'filters' => $data['filters'],
        ]);
    }
}
