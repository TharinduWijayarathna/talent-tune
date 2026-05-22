# Frontend

## Structure

```
resources/js/
├── app.ts                 # Inertia bootstrap, global CSS imports
├── pages/                 # Inertia page components (mirror routes)
│   ├── home/              # Marketing + registration
│   ├── admin/
│   ├── institution/
│   ├── lecturer/
│   ├── student/
│   ├── auth/
│   └── settings/
├── layouts/               # AppSidebarLayout, auth layouts, marketing
├── components/
│   ├── ui/                # shadcn-style primitives (Card, Button, …)
│   ├── marketing/         # Nav, footer, pricing, how-it-works modal
│   ├── viva/              # VivaSessionPanel (shared demo + attend UI)
│   └── VivaSuiteLogo.vue
├── composables/           # useAppearance, useMarketingHome, useHowItWorksDemo
├── lib/                   # chartTheme, utils
└── routes/                # Wayfinder-generated route helpers
```

## Two UI systems

### Marketing (`marketing-page`)

- **CSS:** `resources/css/marketing-home.css` (large single file, light/dark via `html.dark`).
- **Pages:** `LandingPage.vue`, `RegisterInstitution.vue`, etc.
- **Fonts:** Syne (UI), DM Serif Display (headings).
- **Components:** `MarketingNav`, `MarketingFooter`, `HeroVivaPanel`, `MarketingHowItWorks` (Teleport modal), `MarketingWorkspace`, `MarketingPricing`.

### Application shell (`viva-app`)

- **CSS:** `resources/css/app.css` (Tailwind + design tokens), `resources/css/app-shell.css` (overrides for shadcn inside `.viva-app`).
- **Layout:** `AppSidebarLayout` → sidebar navigation by role.
- **Content wrapper class:** `viva-app-content` on page roots.
- **Stat cards:** `VivaStatCard.vue` + shell CSS for KPI alignment.

Do not mix marketing CSS on app pages or vice versa without scoping.

## Key shared component: `VivaSessionPanel`

Used by:

- Marketing hero demo (`HeroVivaPanel.vue`)
- Student live viva (`VivaAttend.vue`)
- How-it-works modal (step 3)

**Styles:** `resources/css/viva-session-panel.css` (imported in `app.ts`).

**Props:** session title, student info, question text, status (`idle` | `speaking` | `recording` | `evaluating` | `feedback`), transcript, waveform, progress.

## Student viva attend page

`student/VivaAttend.vue` — orchestrates:

- Document upload state
- Question loop (TTS play → record voice → upload → evaluate)
- Calls Wayfinder/API routes for generation endpoints
- Binds to `VivaSessionPanel`

Requires microphone permissions in browser; uses RecordRTC dependency.

## Charts

Admin/institution/lecturer/student dashboards use **vue3-apexcharts** with theme from `resources/js/lib/chartTheme.ts`.

## Appearance (light/dark)

- `useAppearance` composable + `HandleAppearance` middleware.
- Marketing and app both respect `html.dark` / system preference.
- Marketing how-it-works modal defines its own CSS variables on `.how-modal-backdrop` because Teleport renders outside `.marketing-page`.

## Wayfinder routes

Type-safe URL helpers imported from `@/routes` and `@/actions`—regenerate when routes change:

```bash
php artisan wayfinder:generate
```

## Build commands

```bash
npm run dev      # Vite HMR
npm run build    # Production assets
```

## Page ↔ backend mapping (examples)

| Vue page | Controller |
|----------|------------|
| `home/LandingPage.vue` | `HomeController@index` |
| `lecturer/CreateViva.vue` | `LecturerController@createViva` |
| `student/VivaAttend.vue` | `StudentController@attendViva` |
| `admin/Dashboard.vue` | `AdminDashboardController@index` |
| `institution/CompleteSubscription.vue` | `InstitutionSubscriptionController@show` |

When adding a page: create Vue file, controller method, route name, run Wayfinder, add sidebar link in `AppSidebar.vue` if needed.
