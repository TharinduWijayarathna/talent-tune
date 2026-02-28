<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use App\Services\Application\StripeSubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionPaymentController extends Controller
{
    public function __construct(
        protected StripeSubscriptionService $stripeService
    ) {}

    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (! $institution) {
            abort(403, 'Institution context required.');
        }

        return $institution;
    }

    protected function authorizeInstitution(?User $user, ?Institution $institution): void
    {
        if (! $user) {
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
     * Show the payment / subscription page for the institution.
     */
    public function index(Request $request): Response|array
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $institution->refresh();
        $subscription = $this->stripeService->getSubscriptionInfo($institution);

        $payload = [
            'institution' => [
                'name' => $institution->name,
                'subscription_status' => $institution->subscription_status,
            ],
            'subscription' => null,
        ];

        if ($subscription) {
            $payload['subscription'] = [
                'status' => $subscription['status'],
                'current_period_end' => $subscription['current_period_end'],
                'cancel_at_period_end' => $subscription['cancel_at_period_end'],
            ];
        }

        return Inertia::render('institution/Payment', $payload);
    }

    /**
     * Cancel subscription at the end of the current billing period.
     */
    public function cancel(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        if ($institution->subscription_status !== 'active') {
            return redirect()->route('institution.payment')->with('error', 'No active subscription to cancel.');
        }

        $ok = $this->stripeService->cancelAtPeriodEnd($institution);
        if (! $ok) {
            return redirect()->route('institution.payment')->with('error', 'Unable to cancel subscription. Please try again or contact support.');
        }

        $subscription = $this->stripeService->getSubscriptionInfo($institution);
        $periodEnd = $subscription['current_period_end'] ?? null;

        return redirect()->route('institution.payment')->with('success', 'Your subscription will end at the end of the current billing period.');
    }
}
