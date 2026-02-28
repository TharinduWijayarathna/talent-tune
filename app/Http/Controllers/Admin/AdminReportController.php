<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminReportController extends Controller
{
    /**
     * Reports index page (list of available reports).
     */
    public function index()
    {
        return Inertia::render('admin/Reports', []);
    }

    /**
     * Download payments report as PDF.
     */
    public function paymentsPdf(Request $request)
    {
        $query = Payment::query()
            ->with('institution:id,name,slug')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->get()->map(fn ($p) => [
            'id' => $p->id,
            'amount_cents' => $p->amount,
            'amount_dollars' => $p->amount / 100,
            'currency' => $p->currency,
            'status' => $p->status,
            'gateway' => $p->gateway,
            'paid_at' => $p->paid_at?->format('Y-m-d H:i'),
            'institution_name' => $p->institution?->name ?? '—',
        ]);

        $totalAmountCents = $payments->sum('amount_cents');
        $generatedAt = now()->format('Y-m-d H:i:s');

        $pdf = Pdf::loadView('reports.admin.payments', [
            'payments' => $payments,
            'totalAmountDollars' => $totalAmountCents / 100,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('talenttune-payments-report-'.date('Y-m-d').'.pdf');
    }

    /**
     * Download profit & loss report as PDF.
     * Revenue: $99 per monthly payment (completed). Cost: $45 per active subscriber (API + Google TTS).
     */
    public function profitLossPdf()
    {
        $subscriptionPrice = (float) config('reports.subscription_price', 99);
        $costPerSubscriber = (float) config('reports.cost_per_subscriber', 45);

        $completedPaymentsCount = Payment::where('status', 'completed')->count();
        $activeSubscribersCount = Institution::where('subscription_status', 'active')->count();

        $revenue = $completedPaymentsCount * $subscriptionPrice;
        $costs = $activeSubscribersCount * $costPerSubscriber;
        $profitLoss = $revenue - $costs;

        $generatedAt = now()->format('Y-m-d H:i:s');

        $pdf = Pdf::loadView('reports.admin.profit-loss', [
            'subscriptionPrice' => $subscriptionPrice,
            'costPerSubscriber' => $costPerSubscriber,
            'completedPaymentsCount' => $completedPaymentsCount,
            'activeSubscribersCount' => $activeSubscribersCount,
            'revenue' => $revenue,
            'costs' => $costs,
            'profitLoss' => $profitLoss,
            'generatedAt' => $generatedAt,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('talenttune-profit-loss-report-'.date('Y-m-d').'.pdf');
    }
}
