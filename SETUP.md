# TASYIIR — Setup

TASYIIR (created by IAM Agency) is a real Laravel 12 + Livewire 3 + Tailwind application. The migration from the Phase 1 mockup is **complete**: every module runs on the database with tenant scoping, and `app/Support/Mock` no longer exists. See "What's real" below for the exact state, and "Not built" for what would be new product surface beyond the original brief.

## Requirements

- PHP 8.2+ with the `sqlite3` / `pdo_sqlite` extensions (XAMPP's default build has them)
- Composer 2
- Node.js 18+ and npm (only for building CSS/JS)

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
```

Then create your own platform-admin login (one-time; there is no UI for this because nothing could approve the first admin):

```bash
php artisan make:platform-admin ops@example.com "Your Name" "a-strong-password"
```

SQLite is the default database. `database/database.sqlite` is created automatically on migrate (create it manually with an empty file if your environment refuses). To switch to MySQL/Postgres later, change `DB_CONNECTION` and the `DB_*` values in `.env` — no code changes needed.

## Run

```bash
php artisan serve
```

Then open http://localhost:8000 — you'll be redirected to the login page.

For live CSS/JS rebuilding during development, run `npm run dev` in a second terminal (Vite HMR). Otherwise the compiled assets from `npm run build` are used.

The monthly payment rollover (`php artisan enrollments:rollover`) is scheduled daily at 00:05; run `php artisan schedule:work` locally or add the Laravel scheduler to cron in production. The same rollover also runs lazily whenever the Dashboard, Enrollments, Students, Payments or Reports pages load, so nothing breaks without cron.

## Seeded logins

Two tenants are seeded so tenant isolation can be verified. Password for every account is `password`.

| Tenant | Role | Login email | Name |
|---|---|---|---|
| مركز النجاح للتكوين (`najah`) | مدير المركز (owner) | `admin@najah.test` | محمد الفاسي |
| | إدارية / استقبال | `reception@najah.test` | سعاد بنعمر |
| | محاسب | `accountant@najah.test` | خالد الصقلي |
| أكاديمية المستقبل للغات (`moustaqbal`) | مدير المركز (owner) | `admin@moustaqbal.test` | سارة بنعلي |
| | إدارية / استقبال | `reception@moustaqbal.test` | سعاد بنعمر |
| | محاسب | `accountant@moustaqbal.test` | خالد الصقلي |

Each tenant has 8 teachers, 8 courses, 16 groups, 45 students, one enrollment per student, a schedule, 8 attendance sessions per group, payments, expenses, salary balances and a notification feed. Log in as one owner, note a student ID, log in as the other and open `/students/{that-id}` — you get a 404. The same holds for users: an owner's Settings → المستخدمون lists only their own center's accounts.

Note: `migrate:fresh` drops the `sessions` table (database session driver), so everyone is logged out after a reseed — and removes any platform admin you created with `make:platform-admin`, so run it again afterwards.

## Onboarding a new center (two-sided flow)

1. **Public signup** — a prospective center opens `/register-center` (linked from the login page), enters the center name, the owner's name/phone/email and a password. This only files a *request* (`center_signup_requests`, password stored hashed); no tenant, user or login exists yet. They see "تم استلام طلبك بنجاح، سيتم التواصل معك بعد المراجعة". An email can't be used while a request for it is pending or approved, or if it is already a login; a rejected applicant may resubmit.
2. **Platform-admin review** — you (a `make:platform-admin` account, not tied to any tenant) sign in and land on `/admin`. Pending requests can be approved or rejected with a reason. **Approve** creates the tenant (name + generated slug), the owner account (same email/password they signed up with, role مدير المركز) and records who approved it and when. **Reject** creates nothing.
3. **Center is live** — the owner signs in with their signup credentials, lands in an empty center (no demo data) and adds their own staff from Settings → المستخدمون.

Platform admins never enter the tenant app (they are redirected to `/admin`, and every tenant-scoped query returns nothing for them); tenant users of any role get a 403 on `/admin`.

## What's real

Everything below is database-backed and tenant-scoped through `App\Models\Concerns\BelongsToTenant`:

- **Auth** (Breeze + Livewire/Volt): login, logout, remember-me, rate limiting. Center signup is a reviewed request (see "Onboarding a new center"); staff accounts are created by the owner. No password reset by email. A paused account (متوقف) is refused at login with a clear message and signed out of any open session.
- **Multi-tenancy**: `tenant_id` on every business table — users included — with a global scope; route-model binding respects it (cross-tenant IDs → 404).
- **Roles & permissions** (Spatie): three built-in roles — مدير المركز (everything), إدارية / استقبال (students, enrollments, payments, attendance), محاسب (expenses, salaries, reports/statistics) — plus per-center custom roles built from the permission catalog in `App\Support\Permissions`. Every module route carries a `permission:` middleware, the sidebar/dashboard/settings hide what the user can't open, and a denied request shows an Arabic 403 page.
- **Students, Teachers, Courses, Groups** — Livewire lists with URL-bound search/filters/pagination, validated add/edit modals, soft delete with confirmation. Teachers carry a salary structure (ثابت / بالعمولة). Student profile tabs read real enrollments, attendance and payments.
- **Enrollments** — source of truth for a registration's money: price/discount/paid → remaining + status, synced onto the student's badge; `due_date` with a monthly rollover to غير مؤدي; course → group → student form flow.
- **Attendance** — per-group, per-date sheet; one record per (student, date).
- **Schedule** — weekly grid CRUD with room/teacher double-booking rejection and a "تعارض" badge for pre-existing conflicts.
- **Payments** — recording applies to the student's current enrollment; printable A5 receipt at `/payments/{id}/receipt` showing the center's profile.
- **Expenses** — CRUD; category tiles are real current-month sums (shared with Reports through `App\Support\Analytics::expensesThisMonth()`).
- **Salaries** — one running balance per teacher recomputed from the salary structure on load; "تسجيل دفعة" records payouts.
- **Notifications** — event-driven (`App\Models\Notification::notify()` from Enrollments, Payments, Expenses, Salaries); header bell with live unread count, full page with filters and mark-read.
- **Dashboard** — `DashboardController` + `App\Support\Analytics`: live stat cards (trends only where last month is a fair baseline), revenue vs. expenses and student-growth charts for the last 6 months, latest enrollments, today's remaining classes.
- **Reports** — `ReportsController`: six real summary cards (students, enrollments, revenue + collection rate, expenses, salaries, attendance) and the 6-month chart.
- **Statistics** — `StatisticsController`: trends, four charts and the course/teacher leaderboards, with a working period select (`?months=6|3|0`, 0 = since January).
- **Settings** — معلومات المركز (name/tagline/contact, shown in the sidebar, footer and receipts; editable with `manage-settings`, read-only otherwise), الحساب (name, email, password change), and for owners **المستخدمون** (staff accounts: create with an initial password, edit, pause/resume) and **الصلاحيات** (built-in roles with their permission sets, plus custom roles scoped to the center).

## Not built (new product surface, not migration work)

The core feature list from the original brief is complete. Candidates for a next phase:

- **Fully automatic provisioning** — signup is self-serve but gated by platform-admin approval by design; there is no instant activation, email verification, billing or trial logic.
- **Password reset by email** — no mail is sent anywhere; an owner resets a staff member's password from the Team panel.
- **PDF export** on Reports — the button shows a placeholder toast; the printable receipt covers the immediate need.
- **Salary period history** — `salary_payments` holds one running balance per teacher, not monthly payroll records.
- **Role-aware notifications** — notifications are tenant-wide for all staff; `Notification.user_id` already supports per-user targeting.
- The Settings tabs الإشعارات / اللغة / المظهر and the header global search modal are still UI-only.

## Useful commands

```bash
php artisan migrate:fresh --seed   # reset and reseed both tenants (logs everyone out)
php artisan route:list             # all routes
php artisan enrollments:rollover   # run the monthly unpaid rollover by hand
npm run build                      # rebuild Tailwind/JS into public/build
node scripts/sync-vendor-assets.mjs  # re-copy Chart.js, fonts and lucide icons into public/
```

Chart.js is served as a plain script from `public/vendor/chart.js` (not bundled by Vite; Vite empties `public/build` on every build). Fonts live in `public/fonts`, icons in `public/icons`.
