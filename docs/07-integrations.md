# Integrations

## Stripe (subscriptions)

**Package:** `stripe/stripe-php`  
**Service:** `App\Services\Application\StripeSubscriptionService`

### Environment

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_PRICE_ID=price_...    # Recurring monthly price
STRIPE_CURRENCY=usd
```

### Flow

1. Create/retrieve Stripe customer for institution.
2. Create Checkout Session (subscription mode).
3. Redirect user to Stripe Hosted Checkout.
4. Return URL triggers `activateFromCheckoutSession($sessionId)`:
   - Verifies `payment_status`
   - Reads `metadata.institution_id`
   - Updates `subscription_status`, Stripe IDs on institution
5. Institution payment page can sync/cancel at period end.

### Controllers

- `SubscriptionController` — public subscribe flow
- `InstitutionSubscriptionController` — logged-in complete subscription
- `InstitutionPaymentController` — billing management
- `AdminPaymentController` — platform view

### Careful

- Missing `STRIPE_SECRET` or `STRIPE_PRICE_ID` logs warnings; checkout will fail silently or error—handle in staging.
- No documented Stripe **webhook** handler in repo—relying on redirect success + manual sync; do not assume webhooks update status unless you add them.

---

## Google Gemini (AI)

**Services:**

| Service | Role |
|---------|------|
| `GeminiQuestionService` | Question generation |
| `GeminiFileService` | Document upload/analysis |
| `GeminiController` | HTTP API for frontend |
| `RubricService` | Grading rubric logic |

### Environment

```env
GEMINI_API_KEY=
```

### API routes (authenticated)

See `routes/generation.php` — all POST, consumed by Vue during viva attend and lecturer create flows.

### Careful

- API keys are server-side only—never expose in Vite `VITE_*` vars.
- Rate limits and costs are per Google Cloud project—monitor usage.
- Prompt content includes student documents—**PII / FERPA** considerations for production.

---

## Google Text-to-Speech

**Service:** `App\Services\Ai\TTSService`  
**Route:** `POST api/viva/tts`

### Environment

```env
GOOGLE_TTS_API_KEY=
```

Returns audio for question playback in browser.

---

## Dockploy / Vintorr (optional DNS)

**Service:** `App\Services\Application\DockployDomainService`

Called when institution is created/activated to register subdomain on hosting panel.

### Environment

```env
DOCKPLOY_TOKEN=
DOCKPLOY_APPLICATION_ID=
# DOCKPLOY_API_URL=https://panel.vintorr.com
```

If not configured, institution still works locally—manual DNS/`/etc/hosts` required.

---

## PDF generation

**Package:** `barryvdh/laravel-dompdf`

Used in:

- `InstitutionReportController` — student/lecturer PDFs
- `AdminReportController` — payments, profit/loss

Invoked as download responses from admin/institution routes.

---

## Email

Laravel mail (`MAIL_*` in `.env`). Notifications include:

- `InstitutionActivated` — admin credentials
- User credential emails from institution user creation
- `AdminCredentialsSent`

Default local setup often uses `MAIL_MAILER=log`—check `storage/logs` for content.

---

## Redis

Configured in `.env` (`REDIS_*`) but default queue/cache may use **database**. Confirm production `QUEUE_CONNECTION` if background jobs are added later—currently no `app/Jobs` directory.
