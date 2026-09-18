# TASYIIR

TASYIIR (created by IAM Agency) is a multi-tenant SaaS for tutoring / language / coaching centers, in Arabic (RTL). It is a real Laravel 12 + Livewire 3 + Tailwind application with authentication, single-database multi-tenancy, per-center staff accounts with roles & permissions (owner / reception / accountant + custom roles), a reviewed public signup flow (`/register-center` → platform-admin approval at `/admin` → the center goes live), and every module — Students, Teachers, Courses, Groups, Enrollments, Attendance, Schedule, Payments (with printable receipts), Expenses, Salaries, Notifications, Dashboard, Reports, Statistics and Settings — running on the database. The migration from the Phase 1 mockup is complete; the mock data layer has been removed.

See **[SETUP.md](SETUP.md)** for installation, seeded logins (one owner, one receptionist and one accountant per tenant), the module-by-module state and what would be new product surface beyond the original brief.

## Stack

- Laravel 12, Livewire 3 (+ Volt for the login page), Laravel Breeze (Livewire stack)
- Spatie `laravel-permission` for roles
- Tailwind CSS 3 via Vite; Alpine.js (bundled by Livewire) for small UI-only interactions; Chart.js vendored
- SQLite by default; MySQL/Postgres via `.env`

## Project structure

```
app/
  Http/Controllers/
    DashboardController.php                 Dashboard (Analytics-backed)
    ReportsController.php                   Reports cards + chart
    StatisticsController.php                Statistics trends/charts/leaderboards (?months=)
    StudentController.php                   Student profile page + destroy
    PaymentController.php                   Printable payment receipt
  Livewire/{Students,Teachers,Courses,Groups,Enrollments,Attendance,Schedule,
            Payments,Expenses,Salaries,Notifications,Settings}/   Full-page Livewire modules
  Livewire/Settings/{Team,Roles}.php        Owner-only staff accounts + roles panels
  Livewire/Public/CenterSignup.php          Public center signup request (no auth)
  Livewire/Admin/SignupRequests.php         Platform back office: approve/reject requests (creates tenant + owner)
  Console/Commands/MakePlatformAdmin.php    make:platform-admin — bootstrap the operator's login
  Http/Middleware/{EnsurePlatformAdmin,EnsureTenantUser}.php   Keep /admin and the tenant app apart
  Http/Middleware/EnsureUserIsActive.php    Signs out paused (متوقف) accounts
  Livewire/Actions|Forms/                   Breeze login/logout
  Models/                                   Tenant, User (tenant-scoped, roles) + one model per module
  Models/Role.php                           Spatie Role + tenant_id/display_name for per-center custom roles
  Models/CenterSignupRequest.php            Platform-level signup requests (not tenant-scoped)
  Models/Concerns/BelongsToTenant.php       Global tenant scope + auto tenant_id stamping
  Models/Scopes/TenantScope.php
  Support/Analytics.php                     Shared tenant-scoped aggregates (dashboard/reports/statistics/expenses)
  Support/Permissions.php                   Permission catalog + the three built-in roles
  helpers.php                               mad(), ar_date(), initials(), is_active_route()
database/
  migrations/                               Full schema for all modules
  seeders/                                  Permissions + roles, 2 tenants with owner/reception/accountant, full demo data per tenant
resources/
  views/layouts/app.blade.php               Authenticated RTL shell (sidebar, header, toasts)
  views/layouts/guest.blade.php             Login / signup shell
  views/layouts/admin.blade.php             Platform back-office shell
  views/livewire/**                         Module views
  views/{dashboard,reports,statistics}/     Controller-rendered pages
  views/students/show.blade.php             Student profile
  views/payments/receipt.blade.php          Printable receipt
  views/components/                         Blade components (stat-card, status-badge, modal, avatar, icon, ...)
  css/app.css, js/app.js                    Tailwind + Alpine stores / Livewire toast bridge
routes/web.php (permission-gated), routes/auth.php, routes/console.php (enrollments:rollover schedule)
public/vendor/chart.js, public/fonts, public/icons   Vendored assets (no CDN calls at runtime)
```

## History

`NOTES.md` keeps the phase-by-phase status banners plus the Phase 1 write-up explaining why the first pass was a dependency-free mockup and how it was designed to be lifted into real Laravel.
