# Roles and permissions

## User roles

Stored on `users.role` as enum: `student`, `lecturer`, `institution`, `admin`.

| Role | Typical login context | Route prefix |
|------|----------------------|--------------|
| `admin` | Main domain (no institution subdomain required) | `/admin` |
| `institution` | Institution subdomain | `/institution` |
| `lecturer` | Institution subdomain | `/lecturer` |
| `student` | Institution subdomain | `/student` |

Additional user fields used for scoping:

- `institution_id` — Required for all non-admin roles.
- `batch` — Students: which vivas they see.
- `student_id`, `employee_id`, `department` — Display / admin metadata.

## Middleware reference

### `SetInstitutionContext` (global web)

**Always runs** on web requests.

- Detects institution (see [02-architecture.md](./02-architecture.md)).
- Skips *enforcement* for auth paths, `admin/*`, registration, home.
- **Still detects** institution on login page (for role selection on subdomain).
- If no institution and not public route → redirect `home`.
- Platform `admin` users skip institution requirement.

### `EnsureInstitutionAccess`

On `institution`, `lecturer`, `student` route groups.

- Logged-in user's `institution_id` must match request institution.
- Mismatch → **403**.
- User on wrong host → redirect to `{slug}.{APP_DOMAIN}` with same path.

### `EnsureAdminRole`

On `admin` route group.

- User must have `role === 'admin'`.

### `EnsureSubscriptionActive`

Inside `institution.php` for most institution routes (after auth).

- Applies only when `user.role === 'institution'`.
- If `!$institution->hasAccess()` → redirect `institution.complete-subscription`.
- Allowed without subscription: `complete-subscription`, `complete-subscription.checkout`.

`Institution::hasAccess()`:

- `subscription_status === 'active'` **OR**
- `trial_ends_at` in the future.

## Login behavior

`AuthenticatedSessionController` + `LoginResponse`:

- On institution subdomain, login may require **role selection** (`student`, `lecturer`, `institution`) so credentials are validated for the correct role.
- After login, `AuthRedirectService` builds subdomain URL for tenant users.

## Authorization patterns in code

There is **no** unified Policy layer for all resources. Authorization is:

- Middleware (role + institution),
- Controller checks (e.g. `if ($user->role === 'admin')` redirect),
- Service-layer queries filtered by `institution_id` and role.

**Implication:** New endpoints must manually scope queries and check institution—do not assume a global policy exists.

## Data access matrix (summary)

| Data | admin | institution | lecturer | student |
|------|-------|-------------|----------|---------|
| All institutions | ✓ | — | — | — |
| Own institution users | ✓ | ✓ | — | — |
| Own institution vivas | ✓ | read/stats | ✓ (own created) | ✓ (batch) |
| Submissions | ✓ | reported issues | ✓ (their vivas) | ✓ (own) |
| Stripe / billing | ✓ (view payments) | ✓ (own) | — | — |
| AI APIs | ✓* | ✓* | ✓* | ✓* |

\*AI routes require `auth` only; institution isolation depends on viva/submission IDs used in the client and server validation in controllers—verify when extending.

## Guest / public routes

- `/` — Marketing landing
- `/register-institution` — Create institution
- `/login`, password reset, email verification
- `/subscribe/{institution}` — Public subscription checkout for an institution

## Settings routes

Authenticated users (any role): profile, password, appearance, two-factor under `routes/settings.php`.
