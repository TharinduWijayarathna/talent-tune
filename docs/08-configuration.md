# Configuration

## Required environment variables

### Application core

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | Branding (e.g. "Viva Suite") |
| `APP_KEY` | Encryption—run `php artisan key:generate` |
| `APP_URL` | Canonical URL |
| `APP_DEBUG` | **false in production** |
| `APP_DOMAIN` | Base domain for institution subdomains (`talenttune.test` local) |
| `APP_RESERVED_SUBDOMAINS` | Comma list: `www,app,talenttune` |
| `APP_LOCAL_TLD` | Local dev TLD (`.test`) for two-part hosts |

### Database

| Variable | Purpose |
|----------|---------|
| `DB_CONNECTION` | `sqlite` default; use `mysql` in production |
| `DB_*` | Host, database, credentials when not SQLite |

### Session

| Variable | Purpose |
|----------|---------|
| `SESSION_DRIVER` | `database` recommended |
| `SESSION_DOMAIN` | Set for cross-subdomain cookies in production (e.g. `.talenttune.com`) |

**Important:** Subdomain login requires correct cookie domain—misconfiguration causes logout on redirect between `app.domain` and `slug.domain`.

### Stripe (production billing)

| Variable | Purpose |
|----------|---------|
| `STRIPE_KEY` | Publishable |
| `STRIPE_SECRET` | Secret |
| `STRIPE_PRICE_ID` | Monthly price ID |
| `STRIPE_CURRENCY` | Default `usd` |

### AI (viva features)

| Variable | Purpose |
|----------|---------|
| `GEMINI_API_KEY` | Question/evaluation generation |
| `GOOGLE_TTS_API_KEY` | Spoken questions |

Without these, viva attend degrades—verify UX when keys missing.

### Dockploy (optional)

| Variable | Purpose |
|----------|---------|
| `DOCKPLOY_TOKEN` | API token |
| `DOCKPLOY_APPLICATION_ID` | Application ID |

### Mail

| Variable | Purpose |
|----------|---------|
| `MAIL_MAILER`, `MAIL_HOST`, … | Outbound email for credentials |

---

## Local development (Docker)

From root README:

1. Add to `/etc/hosts`: `127.0.0.1 talenttune.test`
2. `cp .env.example .env`
3. `docker compose up --build`
4. Open `http://talenttune.test`
5. `docker compose exec app php artisan migrate --force`

Institution subdomains locally: `127.0.0.1 my-uni.talenttune.test` (add per institution slug).

---

## Config files

| File | Contents |
|------|----------|
| `config/domain.php` | Domain, reserved subdomains, local TLD |
| `config/services.php` | Third-party credentials (if used) |
| `config/filesystems.php` | Storage disks |

---

## Frontend build

| Variable | Purpose |
|----------|---------|
| `VITE_APP_NAME` | Passed to client |

Do **not** put secrets in `VITE_*` variables.

---

## PHP / Node versions

- PHP ^8.2 (README mentions 8.3 in Docker)
- Node >= 22 (`package.json` engines)

---

## Artisan commands (common)

```bash
php artisan migrate
php artisan db:seed          # if seeders exist
php artisan wayfinder:generate
php artisan config:cache     # production only
php artisan route:cache      # production only
```

---

## Trusted proxies

`bootstrap/app.php` sets `trustProxies(at: '*')` for reverse proxy SSL/host headers—required behind load balancers; restrict in hardened production if needed.
