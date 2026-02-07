<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminPaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected AdminPaymentService $adminPaymentService
    ) {}

    /**
     * Display a listing of all payments (admin only).
     */
    public function index(Request $request)
    {
        $data = $this->adminPaymentService->getPaymentsWithFilters($request);

        return Inertia::render('admin/Payments', [
            'payments' => $data['payments'],
            'stats' => $data['stats'],
            'filters' => $data['filters'],
        ]);
    }
}
