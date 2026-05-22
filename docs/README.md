# Viva Suite — Documentation

Internal documentation for developers and operators working on **Viva Suite** (formerly TalentTune). Use this folder to understand what the product does, how it is built, and what to treat carefully when changing or deploying it.

## Quick links

| Document | What you'll learn |
|----------|-------------------|
| [01-overview.md](./01-overview.md) | Product purpose, users, and core capabilities |
| [02-architecture.md](./02-architecture.md) | Stack, request flow, multi-tenancy, route layout |
| [03-roles-and-permissions.md](./03-roles-and-permissions.md) | Roles, middleware, who can access what |
| [04-domain-model.md](./04-domain-model.md) | Database entities, relationships, status fields |
| [05-core-flows.md](./05-core-flows.md) | End-to-end flows: registration, viva, AI, billing |
| [06-frontend.md](./06-frontend.md) | Vue/Inertia pages, marketing vs app UI, key components |
| [07-integrations.md](./07-integrations.md) | Stripe, Gemini, Google TTS, Dockploy, PDF reports |
| [08-configuration.md](./08-configuration.md) | Environment variables, domains, local Docker |
| [09-operations-and-caveats.md](./09-operations-and-caveats.md) | **Required care**: tenancy, security, AI, subscriptions |

## Product in one sentence

**Viva Suite** is a multi-tenant SaaS platform where **institutions** onboard via subdomain, manage **lecturers** and **students**, run **viva** (oral exam) sessions with **document upload + voice answers**, and use **AI** (Gemini + TTS) to generate questions, evaluate answers, and support grading—with **Stripe** subscriptions and platform **admin** oversight.

## Repository entry points

| Area | Path |
|------|------|
| HTTP routes | `routes/web.php` → `site.php`, `admin.php`, `institution.php`, `lecturer.php`, `student.php`, `generation.php`, `settings.php` |
| Models | `app/Models/` |
| Business logic | `app/Services/Application/`, `app/Services/Admin/`, `app/Services/Ai/` |
| Controllers | `app/Http/Controllers/` |
| Middleware | `app/Http/Middleware/` |
| Frontend pages | `resources/js/pages/` |
| Marketing UI | `resources/css/marketing-home.css`, `resources/js/pages/home/LandingPage.vue` |
| App shell UI | `resources/css/app-shell.css`, `resources/css/app.css` |
| Viva session UI | `resources/js/components/viva/VivaSessionPanel.vue`, `resources/css/viva-session-panel.css` |

## Related assets

- [figma-wireframe-spec.md](./figma-wireframe-spec.md) — Early wireframe notes (may be partially outdated).
- Root [README.md](../README.md) — Docker run instructions for local development.

## Keeping docs current

When you add a **new role**, **route group**, **external integration**, or change **tenancy rules**, update the matching doc and this index. The highest-risk areas are documented in [09-operations-and-caveats.md](./09-operations-and-caveats.md)—read that before production changes.
