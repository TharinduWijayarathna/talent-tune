<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Services\Application\StripeSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionSubscriptionController extends Controller
{
    public function __construct(
        protected StripeSubscriptionService $stripeService
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $institution = $request->user()->institution;
        if (! $institution) {
            abort(403);
        }
        if ($institution->hasAccess()) {
            return redirect()->route('institution.dashboard');
        }

        return Inertia::render('institution/CompleteSubscription', [
            'institution' => [
                'id' => $institution->id,
                'name' => $institution->name,
                'slug' => $institution->slug,
            ],
            'trial_ended' => $institution->trial_ends_at && $institution->trial_ends_at->isPast(),
        ]);
    }

    public function checkout(Request $request)
    {
        $institution = $request->user()->institution;
        if (! $institution) {
            abort(403);
        }
        if ($institution->hasAccess()) {
            return redirect()->route('institution.dashboard');
        }
        $baseSuccessUrl = URL::route('subscription.success', ['institution' => $institution->slug]);
        $successUrl = $baseSuccessUrl.'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = URL::route('institution.complete-subscription');
        $url = $this->stripeService->createCheckoutSession($institution, $successUrl, $cancelUrl);
        if (! $url) {
            return back()->with('error', 'Unable to start checkout. Please try again or contact support.');
        }

        return redirect()->away($url);
    }
}
