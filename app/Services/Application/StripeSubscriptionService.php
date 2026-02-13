<?php

namespace App\Services\Application;

use App\Models\Institution;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
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

    public function activateFromCheckoutSession(string $sessionId): ?Institution
    {
        if (! $this->stripe) {
            return null;
        }
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['subscription']]);
        } catch (\Throwable $e) {
            Log::warning('Stripe retrieve session failed', ['session_id' => $sessionId, 'error' => $e->getMessage()]);
            return null;
        }
        if ($session->payment_status !== 'paid' || ! $session->subscription) {
            return null;
        }
        $institutionId = $session->metadata->institution_id ?? null;
        if (! $institutionId) {
            return null;
        }
        $institution = Institution::find($institutionId);
        if (! $institution) {
            return null;
        }
        if ($institution->subscription_status === 'active') {
            return $institution;
        }
        $subscriptionId = is_string($session->subscription) ? $session->subscription : $session->subscription->id;
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
        return $institution;
    }
}
