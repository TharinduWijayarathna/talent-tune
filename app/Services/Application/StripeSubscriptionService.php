<?php

namespace App\Services\Application;

use App\Models\Institution;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeSubscriptionService
{
    protected ?StripeClient $stripe = null;

    public function __construct()
    {
        $secret = config('stripe.secret');
        $this->stripe = $secret ? new StripeClient($secret) : null;
    }

    public function getOrCreateCustomer(Institution $institution): ?string
    {
        if ($institution->stripe_customer_id) {
            return $institution->stripe_customer_id;
        }
        if (! $this->stripe) {
            return null;
        }
        $email = $institution->email ?? ($institution->settings['email'] ?? null);
        if (! $email) {
            return null;
        }
        try {
            $customer = $this->stripe->customers->create([
                'email' => $email,
                'name' => $institution->name,
                'metadata' => ['institution_id' => (string) $institution->id],
            ]);
            $institution->update(['stripe_customer_id' => $customer->id]);

            return $customer->id;
        } catch (\Throwable $e) {
            Log::error('Stripe create customer failed', ['institution_id' => $institution->id, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function createCheckoutSession(Institution $institution, string $successUrl, string $cancelUrl): ?string
    {
        $priceId = config('stripe.price_id');
        if (! $this->stripe || ! $priceId) {
            Log::warning('Stripe not configured: missing secret or STRIPE_PRICE_ID');

            return null;
        }
        $customerId = $this->getOrCreateCustomer($institution);
        if (! $customerId) {
            return null;
        }
        try {
            $session = $this->stripe->checkout->sessions->create([
                'customer' => $customerId,
                'mode' => 'subscription',
                'line_items' => [['price' => $priceId, 'quantity' => 1]],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => ['institution_id' => (string) $institution->id],
                'subscription_data' => ['metadata' => ['institution_id' => (string) $institution->id]],
            ]);

            return $session->url;
        } catch (\Throwable $e) {
            Log::error('Stripe create checkout session failed', ['institution_id' => $institution->id, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Activate institution after successful payment. Uses session metadata; falls back to $institutionFromRoute when provided (e.g. from success URL).
     */
    public function activateFromCheckoutSession(string $sessionId, ?Institution $institutionFromRoute = null): ?Institution
    {
        if (! $this->stripe) {
            Log::warning('Stripe activateFromCheckoutSession: Stripe not configured');

            return null;
        }
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['subscription']]);
        } catch (\Throwable $e) {
            Log::warning('Stripe retrieve session failed', ['session_id' => $sessionId, 'error' => $e->getMessage()]);

            return null;
        }

        if ($session->payment_status !== 'paid') {
            Log::info('Stripe session not paid yet', ['session_id' => $sessionId, 'payment_status' => $session->payment_status]);

            return null;
        }

        $institutionId = null;
        if ($session->metadata !== null) {
            $meta = $session->metadata;
            $institutionId = is_array($meta) ? ($meta['institution_id'] ?? null) : ($meta->institution_id ?? null);
            if ($institutionId !== null && ! is_scalar($institutionId)) {
                $institutionId = (string) $institutionId;
            }
        }

        $institution = null;
        if ($institutionId) {
            $institution = Institution::find($institutionId);
        }
        if (! $institution && $institutionFromRoute) {
            $institution = $institutionFromRoute;
        }
        if (! $institution) {
            Log::warning('Stripe activateFromCheckoutSession: no institution found', ['session_id' => $sessionId, 'metadata_institution_id' => $institutionId]);

            return null;
        }

        if ($institution->subscription_status === 'active') {
            return $institution;
        }

        $subscriptionId = $session->subscription ? (is_string($session->subscription) ? $session->subscription : $session->subscription->id) : null;
        $institution->update([
            'stripe_subscription_id' => $subscriptionId,
            'subscription_status' => 'active',
        ]);
        Payment::firstOrCreate(
            ['gateway' => 'stripe', 'external_id' => $session->id],
            [
                'institution_id' => $institution->id,
                'currency' => strtolower($session->currency ?? 'usd'),
                'amount' => (int) ($session->amount_total ?? 0),
                'status' => 'completed',
                'paid_at' => now(),
                'metadata' => ['subscription_id' => $subscriptionId],
            ]
        );
        Log::info('Institution subscription activated', ['institution_id' => $institution->id, 'session_id' => $sessionId]);

        return $institution;
    }
}
