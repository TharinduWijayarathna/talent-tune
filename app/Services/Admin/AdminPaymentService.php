<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Http\Request;

class AdminPaymentService
{
    /**
     * Get paginated payments with filters and stats.
     */
    public function getPaymentsWithFilters(Request $request): array
    {
        $query = Payment::query()
            ->with('institution:id,name,slug')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('institution', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate(15)->through(function ($payment) {
            return [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'gateway' => $payment->gateway,
                'external_id' => $payment->external_id,
                'paid_at' => $payment->paid_at?->toISOString(),
                'created_at' => $payment->created_at->toISOString(),
                'institution' => $payment->institution
                    ? [
                        'id' => $payment->institution->id,
                        'name' => $payment->institution->name,
                        'slug' => $payment->institution->slug,
                    ]
                    : null,
            ];
        });

        $stats = [
            'total' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'total_amount' => Payment::where('status', 'completed')->sum('amount'),
        ];

        return [
            'payments' => $payments,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ];
    }
}
