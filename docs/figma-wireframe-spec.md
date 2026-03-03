# TalentTune – Figma Wireframe Specification

Use this document to build **low-fidelity wireframes** for the entire application in Figma. Each section describes one screen: layout regions, key UI elements, and suggested placeholder content.

**Suggested Figma setup**

- Frame size: **1440×900** (desktop) or **375×812** (mobile) as needed
- Use **gray rectangles** and **text labels** for placeholders
- Reuse one **App Shell** component (sidebar + header + content) for all authenticated screens
- Group frames by section: Public, Auth, Institution, Student, Lecturer, Admin, Settings

---

## Global patterns

### App shell (authenticated)

- **Sidebar (left, ~256px)**
    - Logo at top
    - Nav items (icon + label): Dashboard, [role-specific items]
    - User menu at bottom (avatar + name, dropdown: Profile, Settings, Log out)
    - Collapsible to icon-only
- **Header (top bar)**
    - Sidebar toggle
    - Breadcrumbs (e.g. Dashboard > Batches)
- **Content area**
    - Scrollable; padding ~16–24px
    - Page title (h1) + optional description
    - Then page-specific content

### Auth layout

- Centered card (or split layout: left branding, right form)
- Title + short description
- Form fields + primary button + secondary link (e.g. Sign up, Forgot password)

### Public (marketing) layout

- Site header: logo, nav (Home, Features, Pricing, About), [Login] [Register institution]
- Full-width sections; CTA buttons

---

## 1. Public / marketing

### 1.1 Landing page

- **Header**: Logo, nav (Home, Features, Pricing, About), Login, Register institution
- **Hero**: Headline, subheadline, primary CTA, optional illustration
- **How it works**: 3 steps (e.g. Register institution → Get subdomain → Manage vivas)
- **Features grid**: 4–6 feature cards (icon, title, short text)
- **Stats**: 3–4 metrics (e.g. Institutions, Students, Sessions)
- **CTA**: Final call-to-action block
- **Footer**: Links, copyright

### 1.2 Home page (institution subdomain)

- Same header as landing
- Welcome / dashboard-style content for institution context
- Links to login or key actions

### 1.3 Institution pending

- Message: institution not yet active; contact admin or wait for activation

### 1.4 Institution payment required

- Message: subscription required; CTA to subscribe or pay

### 1.5 Features

- Header
- Section title + list or grid of features with short descriptions

### 1.6 Pricing

- Header
- Pricing cards (e.g. plan name, price, features, CTA)

### 1.7 About

- Header
- Section title + body text (company story, team, etc.)

### 1.8 Register institution

- Form: institution name, slug, email, contact person, phone, address, etc.
- Submit button
- Link to login if already registered

### 1.9 Register institution success

- Success message + institution id/name
- Link to login or home

### 1.10 Subscribe institution

- Plan selection (if multiple)
- Payment / checkout form or summary
- Success: thank you + next steps

---

## 2. Auth

### 2.1 Login

- **Layout**: Auth layout (centered card or split)
- **Optional**: Role selection (3 cards: Student, Lecturer, Institution Admin) – when on institution subdomain
- **Form**: Email (input), Password (input), “Remember me” (checkbox), “Log in” (button)
- **Links**: Forgot password?, Don’t have an account? Sign up
- **Optional**: Status/error message area at top

### 2.2 Register (user)

- Auth layout
- Form: name, email, password, confirm password, role (if applicable)
- Submit, link to login

### 2.3 Forgot password

- Auth layout
- Form: email
- Submit, link back to login

### 2.4 Reset password

- Auth layout
- Form: email (read-only), password, confirm password
- Submit

### 2.5 Verify email

- Auth layout
- Message: verify your email; resend link button

### 2.6 Confirm password

- Auth layout
- Form: password
- Submit (for sensitive actions)

### 2.7 Two-factor challenge

- Auth layout
- Form: 2FA code input
- Submit

---

## 3. Dashboard (role redirect)

- Single frame: “Redirects to role dashboard” (or show 4 variants: Admin / Institution / Lecturer / Student dashboard)

---

## 4. Institution (prefix `/institution`)

**Shell**: App shell with sidebar: Dashboard, Payment, Batches, Lecturers, Students, Support, Reported issues.

### 4.1 Institution dashboard

- **Content**: Page title “Dashboard”
- **Blocks**: 3–4 stat cards or placeholder cards (e.g. Batches, Lecturers, Students, Support)
- **Below**: Large content area (table or list placeholder)

### 4.2 Complete subscription

- Short form or steps to complete payment/subscription
- Success state optional

### 4.3 Payment

- **Content**: Title “Payment” / “Subscription”
- **Block**: Current plan, status, renew/cancel
- **Block**: Payment history (table: date, amount, status)

### 4.4 Batches

- **Content**: Title “Manage Batches”, description
- **Card “Add batch”**: Input (batch name), button “Add Batch”
- **Card “All batches”**: List of rows – icon, batch name, student count, created date, delete button; empty state “No batches yet”

### 4.5 Lecturers

- **Content**: Title “Manage Lecturers”, description
- **Toolbar**: Search input, button “Add Lecturer”
- **List**: Rows – avatar/icon, name, email, employee id, department, status, total sessions, actions (Edit, Delete); or table columns
- **Empty state** if no lecturers

### 4.6 Add lecturer

- **Content**: Title “Add Lecturer”, breadcrumbs
- **Form**: Name, email, employee id, department, etc., Submit

### 4.7 Edit lecturer

- Same as Add lecturer, pre-filled; title “Edit Lecturer”

### 4.8 Students

- **Content**: Title “Manage Students”, description
- **Toolbar**: Search, “Add Student”
- **List/table**: Name, email, batch, status, actions (Edit, Delete)
- **Empty state**

### 4.9 Add student

- **Content**: Title “Add Student”
- **Form**: Name, email, batch (select), etc., Submit

### 4.10 Edit student

- Same as Add student; title “Edit Student”

### 4.11 Support (list)

- **Content**: Title “Support”
- **Toolbar**: “Create ticket”
- **List**: Ticket id, subject, status, date; click to view

### 4.12 Support create

- **Content**: Title “Create ticket”
- **Form**: Subject, message, attachments (optional), Submit

### 4.13 Support show

- **Content**: Breadcrumbs, ticket subject
- **Block**: Ticket detail (status, date, messages/thread)
- **Block**: Reply form (message, submit)

### 4.14 Reports

- **Content**: Title “Reports”
- **Block**: Buttons or links (e.g. “Students PDF”, “Lecturers PDF”)

### 4.15 Reported issues (list)

- **Content**: Title “Reported issues”
- **List**: Issue id, reporter, status, date; link to view

### 4.16 Reported issue show

- **Content**: Breadcrumbs, issue title
- **Block**: Detail (description, status, reporter, actions: review, escalate)

---

## 5. Student (prefix `/student`)

**Shell**: App shell with sidebar: Dashboard, Viva Sessions, Report issue.

### 5.1 Student dashboard

- **Content**: Title “Dashboard”
- **Blocks**: 2–3 summary cards (e.g. upcoming vivas, completed)
- **List**: Upcoming viva sessions (title, date, “Attend” or “View”)

### 5.2 Viva sessions (list)

- **Content**: Title “Viva Sessions”, description
- **Cards**: Per session – title, description, date, time, lecturer, batch, status badge (Open / Upcoming / Closed), “Attend Viva” or “View my answers” or disabled; optional materials, result (marks/grade)

### 5.3 Viva attend

- **Content**: Title “Attend Viva” / session title, breadcrumbs
- **Block**: Instructions or timer
- **Block**: Questions / recording UI (placeholder for voice + doc upload)
- **Actions**: Submit, exit

### 5.4 Viva submission

- **Content**: Title “Submission” / “My answers”
- **Block**: View-only submission (documents, answers, grade if released)

### 5.5 Report issue (list)

- **Content**: Title “Report issue”
- **Toolbar**: “Create issue”
- **List**: My issues (subject, status, date)

### 5.6 Report issue create

- **Content**: Title “Report issue”
- **Form**: Subject, description, Submit

---

## 6. Lecturer (prefix `/lecturer`)

**Shell**: App shell with sidebar: Dashboard, Create Viva, My Sessions, Report issue.

### 6.1 Lecturer dashboard

- **Content**: Title “Dashboard”
- **Blocks**: Stat cards (e.g. sessions, students)
- **List**: My vivas (title, date, status, “View” / “Close”)

### 6.2 Vivas (list)

- **Content**: Title “My Sessions” / “Vivas”
- **Toolbar**: “Create Viva”
- **List**: Cards or rows – title, date, batch, status, “View”, “Close”, “Add late student”

### 6.3 Create viva

- **Content**: Title “Create Viva”
- **Form**: Title, description, batch (select), date/time, due date, materials (optional), Submit

### 6.4 Show viva (detail)

- **Content**: Title, breadcrumbs
- **Block**: Session info (batch, date, due, status)
- **Block**: List of students (attended, marks, grade)
- **Actions**: Close viva, Add late student

### 6.5 Report issue (list)

- Same pattern as Student “Report issue (list)”

### 6.6 Report issue create

- Same pattern as Student “Report issue create”

---

## 7. Admin (prefix `/admin`)

**Shell**: App shell with sidebar: Dashboard, System Users (TalentTune admins), Institutions, Institutional Users (sub: Admin mgmt, Student mgmt, Lecturer mgmt), Payments, Support Tickets, Reports.

### 7.1 Admin dashboard

- **Content**: Title “Dashboard”
- **Blocks**: Stat cards (institutions, users, payments)
- **Block**: Recent activity or table placeholder

### 7.2 TalentTune admins (list)

- **Content**: Title “System Users” / “TalentTune admins”
- **Toolbar**: “Add admin”
- **List**: Email, name, added date, actions

### 7.3 Add TalentTune admin

- **Content**: Title “Add admin”
- **Form**: Email (or invite), Submit

### 7.4 Institutions (list)

- **Content**: Title “Institutions”
- **Toolbar**: Search
- **Summary**: Pending count, Active count
- **List**: Cards or table – name, slug, email, status (active/trial/pending), subscription, “View” link
- **Empty state**

### 7.5 Institution detail (view)

- **Content**: Breadcrumbs, institution name
- **Block**: Details (name, slug, email, contact, phone, address, status, trial end, subscription)
- **Actions**: Edit, (optional) Destroy

### 7.6 Edit institution

- **Content**: Title “Edit institution”
- **Form**: Same fields as detail, Submit

### 7.7 Users (list)

- **Content**: Title “Institutional Users” (or “Students” / “Lecturers” / “Admin management” per tab)
- **Toolbar**: Search
- **List**: Table – name, email, institution, role, status, “Edit”

### 7.8 Edit user

- **Content**: Title “Edit user”
- **Form**: Name, email, role, institution (if applicable), status, Submit

### 7.9 Payments (list)

- **Content**: Title “Payments”
- **List**: Table – id, institution, amount, date, status, “View”

### 7.10 Payment detail

- **Content**: Breadcrumbs, payment id
- **Block**: Amount, date, institution, status, method

### 7.11 Reports

- **Content**: Title “Reports”
- **Block**: Links/buttons (e.g. “Payments PDF”, “Profit/loss PDF”)

### 7.12 Support tickets (list)

- **Content**: Title “Support tickets”
- **List**: Ticket id, institution, subject, status, date, “View”

### 7.13 Support ticket detail

- **Content**: Breadcrumbs, ticket subject
- **Block**: Messages thread
- **Block**: Reply form, status update (dropdown)

---

## 8. Settings (prefix `/settings`)

**Shell**: Settings layout – sidebar: Profile, Password, Two-Factor, Appearance. Content area for form.

### 8.1 Profile

- **Content**: Title “Profile”
- **Form**: Name, email (read-only or editable), avatar (optional), Submit

### 8.2 Password

- **Content**: Title “Password”
- **Form**: Current password, New password, Confirm password, Submit

### 8.3 Two-factor

- **Content**: Title “Two-factor authentication”
- **Block**: Enable/disable 2FA, QR or code setup, verify

### 8.4 Appearance

- **Content**: Title “Appearance”
- **Block**: Theme options (e.g. Light; Dark if supported)

---

## Figma frame checklist

Create one frame per row below. Name frames by section + screen (e.g. `Public – Landing`, `Auth – Login`).

| Section     | Frames                                                                                                                                                                                                                          |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Public      | Landing, Home, Institution pending, Institution payment required, Features, Pricing, About, Register institution, Register institution success, Subscribe institution                                                           |
| Auth        | Login, Register, Forgot password, Reset password, Verify email, Confirm password, Two-factor challenge                                                                                                                          |
| Dashboard   | Dashboard (or 4 role variants)                                                                                                                                                                                                  |
| Institution | Dashboard, Complete subscription, Payment, Batches, Lecturers, Add lecturer, Edit lecturer, Students, Add student, Edit student, Support list, Support create, Support show, Reports, Reported issues list, Reported issue show |
| Student     | Dashboard, Viva sessions list, Viva attend, Viva submission, Report issue list, Report issue create                                                                                                                             |
| Lecturer    | Dashboard, Vivas list, Create viva, Show viva, Report issue list, Report issue create                                                                                                                                           |
| Admin       | Dashboard, TalentTune admins list, Add admin, Institutions list, Institution detail, Edit institution, Users list, Edit user, Payments list, Payment detail, Reports, Support tickets list, Support ticket detail               |
| Settings    | Profile, Password, Two-factor, Appearance                                                                                                                                                                                       |

Total: **~64 frames** (adjust if you merge list/detail or split variants).

Use **Auto Layout** and **Components** for sidebar, header, cards, and form blocks to keep wireframes consistent and easy to update.
