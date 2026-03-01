# TalentTune

TalentTune is a multi-tenant web app for institutions to run **viva** (oral exam) sessions. Lecturers create vivas with instructions and materials; students attend, upload documents and voice responses, and submit. Includes AI-assisted generation (Gemini, TTS), PDF reports, Stripe subscriptions, and support ticketing.

**Roles**

- **TalentTune Admin** — Institutions, users, payments, support tickets, reports.
- **Institution** — Dashboard, batches, lecturers & students, subscription (Stripe), support.
- **Lecturer** — Create and manage vivas, view student submissions (document + voice).
- **Student** — View vivas, attend, upload document/voice, complete submission.

**Stack** — Laravel 12, Vue 3 (Inertia), Fortify, Stripe, Redis; PHP 8.3, Node 22 for build.

---

## Running the app (Docker only)

### Prerequisites

- Docker (and Docker Compose)

### 1. Vhost (hosts file)

Add this line so the app is reachable at `talenttune.test`:

```text
127.0.0.1 talenttune.test
```

- **macOS / Linux:** edit `/etc/hosts`
- **Windows:** edit `C:\Windows\System32\drivers\etc\hosts`

### 2. Environment

```bash
cp .env.example .env
```

In `.env` set at least:

- `APP_URL=http://talenttune.test`
- `APP_DOMAIN=talenttune.test` (already set in example for local)
- For Stripe (subscriptions): `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_PRICE_ID`
- Optional: `GEMINI_API_KEY`, `GOOGLE_TTS_API_KEY` for AI/TTS features

Default DB is SQLite; data is persisted via the mounted `./storage` and `./database` volumes.

### 3. Run

```bash
docker compose up --build
```

Open **http://talenttune.test** in the browser.

### Optional: first-run setup

After the container is up you can run migrations and seed (if needed) inside the container:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed   # if you have seeders
```
