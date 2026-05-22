# Overview

## What Viva Suite does

Viva Suite digitizes **oral examinations (vivas)** for educational institutions. Instead of scheduling only in-person vivas, lecturers configure exams online; students upload work (reports, dissertations, projects), then complete a **voice-based session** where an AI-assisted flow asks questions, records answers, transcribes speech, and stores results for lecturer review.

The platform is **multi-tenant**: each institution is isolated by `institution_id`, typically accessed via **`{slug}.{APP_DOMAIN}`** subdomain.

## Who uses it

| Role | Code (`users.role`) | Primary goals |
|------|---------------------|---------------|
| **Platform admin** | `admin` | Manage all institutions, users, payments, support tickets, global reports |
| **Institution admin** | `institution` | Manage subscription, batches, lecturers, students, institution support & reported issues |
| **Lecturer** | `lecturer` | Create/configure vivas, review submissions (documents + voice), close sessions, report issues |
| **Student** | `student` | View assigned vivas (by batch), upload document, attend viva (voice), complete submission |

Public visitors use the **marketing homepage** (`/`) to learn about the product and **register an institution**.

## Core capabilities (must-know)

1. **Institution onboarding** — Self-service registration creates institution + slug + 14-day trial + institution admin user (credentials emailed).
2. **Subdomain tenancy** — Institution context resolved from host, custom domain, or logged-in user; enforced in middleware.
3. **Viva lifecycle** — Lecturer creates viva (instructions, materials, batch, schedule/due dates) → students in that batch attend → submissions stored → viva can auto-close when `due_at` passes.
4. **Student viva session** — Document upload, voice recording per question, AI question generation & answer evaluation via authenticated API routes.
5. **AI stack** — Google Gemini (instructions, questions, evaluation, conversational responses), Google TTS (question audio), optional file handling via Gemini file APIs.
6. **Subscriptions** — Stripe Checkout for institution plans; trial + `subscription_status`; institution routes gated by `EnsureSubscriptionActive`.
7. **Support & issues** — Support tickets (institution ↔ platform admin); reported issues (student/lecturer ↔ institution).
8. **Reporting** — PDF exports (institution reports, admin financial reports) via DomPDF.
9. **Batches** — Students belong to a `batch` string; vivas target a batch so only matching students see them.

## What this is not

- Not a generic LMS — scope is viva/oral exam workflow, not full course management.
- Not real-time video proctoring — focus is voice + document + AI Q&A.
- Webhooks for Stripe are not the primary activation path in code — checkout success URLs and session retrieval activate subscriptions (see integrations doc).

## Branding note

The product is branded **Viva Suite** in UI and marketing. Legacy names (`TalentTune`, `talenttune` in env/domain examples) may still appear in config, routes, or admin labels—treat them as the same platform unless a migration removes them.
