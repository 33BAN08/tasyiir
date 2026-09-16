# PlanZeen — Phase 1 (UI Mockup)

PlanZeen is a multi-tenant SaaS mockup for tutoring / language / coaching centers. This repository contains **Phase 1**: a fully clickable, Arabic-RTL, frontend-only UI built with mock data. There is **no real backend** — no database, no auth, no persistence. See `NOTES.md` for why this is a custom lightweight PHP framework instead of a real Laravel + Livewire install, and for the Phase 2 migration path.

## Requirements

- PHP 8.1+ (no extensions beyond the default CLI build)
- Node.js 18+ (only if you want to rebuild CSS/JS/icon assets — not required to run the app, since the compiled assets are already committed under `public/build/`, `public/fonts/`, and `public/icons/`)

No Composer, no database, no `.env` file needed.

## Running the app

From the project root:

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

Then open `http://127.0.0.1:8000/dashboard` in your browser. The router serves static files (CSS/JS/fonts/icons) directly and routes everything else through `public/index.php`.

## Rebuilding front-end assets (optional)

Only needed if you change `resources/css/app.css`, `tailwind.config.js`, or want to re-vendor JS/fonts/icons.

```bash
npm install
npm run build        # rebuilds Tailwind CSS + re-syncs vendor assets (Alpine.js, Chart.js, lucide icons, Cairo/IBM Plex Mono fonts)
```

Individual scripts:

```bash
npm run build:css     # Tailwind CLI build → public/build/app.css
npm run sync:vendor   # copies alpinejs/chart.js/lucide-static/@fontsource files into public/build, public/icons, public/fonts
```

## Project structure

```
app/
  Routing/Route.php        Minimal Laravel-style router (Route::get(...)->name(...))
  View/BladeLite.php        Blade-like template compiler (compiles .blade.php → cached plain PHP)
  View/View.php              @extends/@section/@yield layout resolution
  Support/Mock/               Centralized mock data generators (one class per domain: Students, Teachers, Courses, Groups, Enrollments, Attendance, Schedule, Payments, Expenses, Salaries, Notifications, Dashboard)
  helpers.php                 view(), asset(), route_url(), mad(), ar_date(), initials(), etc.
public/
  index.php                   Front controller
  router.php                  Router script for PHP's built-in dev server
  build/, fonts/, icons/       Pre-built, self-contained vendor assets (Tailwind CSS output, Alpine.js, Chart.js, lucide icons, Cairo/IBM Plex Mono fonts) — no CDN calls at runtime
resources/
  views/layouts/app.blade.php  Main authenticated-style shell (RTL sidebar, header, toasts)
  views/components/            Reusable Blade-lite components (stat-card, data-table pieces, modal, dropdown-panel, status-badge, avatar, pagination, chart-container, quick-action-card, etc.)
  views/{dashboard,students,teachers,courses,groups,enrollments,attendance,schedule,payments,expenses,salaries,reports,statistics,notifications,settings}/
  css/app.css, js/app.js
routes/web.php                All 16 page routes
storage/framework/views/      Compiled template cache (auto-generated, safe to delete)
```

## Pages / routes

`/dashboard`, `/students`, `/students/{id}`, `/teachers`, `/courses`, `/groups`, `/enrollments`, `/attendance`, `/schedule`, `/payments`, `/expenses`, `/salaries`, `/reports`, `/statistics`, `/notifications`, `/settings`.

## What's mocked

Every number, name, table row, chart series, and notification on every page comes from `app/Support/Mock/*.php` — deterministic generators (no randomness), so the same page always renders the same data. Nothing is written back; forms, "add" buttons, and action menus show a toast confirming the UI action but do not persist anything.

See `NOTES.md` for the full list of what was intentionally left out of Phase 1, and the recommended path to Phase 2.
