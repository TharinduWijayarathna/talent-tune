# Operations and caveats

Read this before changing auth, tenancy, billing, or student viva flows.

## Multi-tenancy (highest risk)

### Always scope by institution

Any new query on `vivas`, `users`, `viva_student_submissions`, tickets, etc. **must** filter by the current institution ID from `$request->attributes->get('institution')` or the authenticated user's `institution_id`.

**Failure mode:** Cross-tenant data leak (critical security bug).

### Subdomain vs user institution

`EnsureInstitutionAccess` blocks user/institution mismatch, but **admin** bypasses institution checks—admin controllers must never expose arbitrary institution data without explicit ID validation.

### Session cookies across subdomains

Production must set `SESSION_DOMAIN` to the parent domain (e.g. `.talenttune.com`) so login persists on `{slug}.talenttune.com`. Wrong domain = auth loops or 403 after redirect.

### Reserved subdomains

Do not register institutions with slugs in `APP_RESERVED_SUBDOMAINS`—they will not resolve as tenants.

---

## Authentication and roles

### Role string is authoritative

Use exact values: `admin`, `institution`, `lecturer`, `student`. Typos in seeds or migrations break redirects and middleware.

### Login role selection

On institution subdomain, login may require `role` in request—test all four flows when changing auth.

### Generated passwords

Institution-created users receive emailed credentials. Passwords are generated server-side—ensure mail works before go-live; users cannot self-recover without password reset flow.

### Two-factor authentication

Fortify 2FA enabled—account for `two-factor-challenge` routes in institution skip lists.

---

## Subscriptions and access

### Trial vs paid

- New institutions get `trial_ends_at` (14 days in `InstitutionService::TRIAL_DAYS`).
- `hasAccess()` = active subscription **OR** active trial.
- Deactivating institution clears trial in `update()`.

### Do not lock out without escape hatch

`EnsureSubscriptionActive` allows `complete-subscription` routes—keep these when adding new institution middleware.

### Stripe sync limitations

Subscription state updates primarily via checkout return and explicit sync calls—not webhooks. If checkout succeeds but redirect fails, institution may appear unpaid—support manual sync in `StripeSubscriptionService`.

---

## Viva lifecycle

### UTC due dates

`closeOverdueVivas()` compares `due_at` in **UTC**. Display timezones in UI but store/compare consistently.

### Batch matching

Students only see vivas where `viva.batch` equals `user.batch`. Empty batch = student may see no vivas—validate on student create.

### Late participation

`allowed_after_close` on submissions—lecturer feature; bypasses normal closed viva rules. Test edge cases.

### Submission JSON (`answers`)

Structure is flexible JSON—frontend and backend must stay in sync when adding fields. Bad migrations can corrupt grading display.

### File storage

Documents and voice files on local disk by default—**backup `storage/`** in production; use S3-compatible disk for horizontal scaling.

Stream routes must check submission belongs to user/lecturer/institution—verify on any new download endpoint.

---

## AI features

### API keys required

Without `GEMINI_API_KEY` / `GOOGLE_TTS_API_KEY`, core viva experience fails partially—handle errors in UI and log server-side.

### Cost and abuse

Authenticated users can hit generation endpoints—rate limit in production; monitor token usage.

### Data privacy

Student documents and voice transcripts are sent to Google APIs—institutions may require DPAs and data processing agreements.

### Prompt injection

Lecturer `instructions` and student uploads influence prompts—treat as untrusted input when hardening.

---

## Admin operations

### Institution deactivate

Setting `is_active = false` should block tenant resolution (`active()` scope)—confirm users cannot log in on subdomain.

### Deleting institutions

Destroy flows may cascade—check `InstitutionController@destroy` and related FK constraints before production deletes.

### Platform admin creation

`AdminAdminController` creates `role=admin` users—protect routes with `EnsureAdminRole` only.

---

## Frontend caveats

### Teleport modals (marketing)

Components teleported to `body` (e.g. `MarketingHowItWorks`, mobile nav) **do not inherit** `.marketing-page` CSS variables—define theme on the modal root (already done for how-it-works).

### Two CSS systems

Changing `app.css` does not affect marketing homepage—update `marketing-home.css` separately.

### Wayfinder drift

Renaming Laravel routes without regenerating Wayfinder breaks TypeScript imports—run `php artisan wayfinder:generate` in CI.

---

## Deployment checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` set
- [ ] Database migrations run
- [ ] `npm run build` assets committed or built in pipeline
- [ ] `SESSION_DOMAIN` for subdomains
- [ ] HTTPS termination + trusted proxies
- [ ] Stripe live keys + price ID
- [ ] Mail provider configured
- [ ] AI API keys and quotas
- [ ] Storage backup / cloud disk
- [ ] Wildcard DNS `*.yourdomain.com` or Dockploy automation
- [ ] `/etc/hosts` or DNS docs for internal testing only

---

## Testing gaps to know

Test suite exists (Pest) but coverage of tenancy and AI paths may be limited—manually test:

1. Register institution → login on subdomain
2. Add student (batch) + lecturer
3. Create viva for batch
4. Student full attend flow with AI
5. Lecturer review streams
6. Trial expiry → subscription checkout
7. Admin institution deactivate

---

## Legacy naming

Code and env may still say `TalentTune`, `talenttune.test`, or `talenttune-admins` routes—grep when rebranding to avoid missing user-facing strings or route names.
