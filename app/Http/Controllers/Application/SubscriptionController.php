<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\Application\StripeSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        protected StripeSubscriptionService $stripeService
    ) {}

    public function show(Institution $institution, Request $request): Response|string
    {
        if (! URL::hasValidSignature($request)) {
            abort(403, 'This payment link is invalid or has expired.');
        }
        if ($institution->subscription_status === 'active') {
            $baseDomain = config('domain.domain');
            return redirect()->away("https://{$institution->slug}.{$baseDomain}/institution/dashboard")
                ->with('info', 'Your subscription is already active.');
        }
        return Inertia::render('home/SubscribeInstitution', [
            'institution' => [
                'id' => $institution->id,
                'name' => $institution->name,
                'slug' => $institution->slug,
            ],
            'checkoutUrl' => route('subscription.checkout', ['institution' => $institution->slug])
                . ($request->getQueryString() ? '?' . $request->getQueryString() : ''),
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'institution_id' => 'required|integer|exists:institutions,id',
        ]);
        if (! URL::hasValidSignature($request)) {
            abort(403, 'This payment link is invalid or has expired.');
        }
        $institution = Institution::findOrFail($validated['institution_id']);
        $successUrl = URL::route('subscription.success', [
            'institution' => $institution->slug,
            'session_id' => '{CHECKOUT_SESSION_ID}',
        ]);
        $cancelUrl = URL::route('subscription.show', ['institution' => $institution->slug])
            . ($request->getQueryString() ? '?' . $request->getQueryString() : '');
        $url = $this->stripeService->createCheckoutSession($institution, $successUrl, $cancelUrl);
        if (! $url) {
            return back()->with('error', 'Unable to start checkout. Please try again or contact support.');
        }
        return redirect()->away($url);
    }

    public function success(Request $request, Institution $institution)
    {
        $sessionId = $request->query('session_id');
        if (! $sessionId) {
            return redirect()->route('home')->with('error', 'Invalid success link.');
        }
        $this->stripeService->activateFromCheckoutSession($sessionId);
        $baseDomain = config('domain.domain') ?: parse_url(config('app.url'), PHP_URL_HOST);
        $scheme = request()->getScheme();
        return redirect()->away("{$scheme}://{$institution->slug}.{$baseDomain}/institution/dashboard")
            ->with('success', 'Payment complete. Your workspace is now active.');
    }
}
