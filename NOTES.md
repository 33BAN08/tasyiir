# Developer Notes — Architecture Decision & Phase 2 Path

## Why this isn't a real Laravel install

The original brief called for real Laravel (Blade + Livewire + Tailwind). During setup, `composer create-project laravel/laravel` and every subsequent `composer install`/`composer require` attempt failed: this sandbox's organization-level network policy blocks `packagist.org`, `repo.packagist.org`, and `getcomposer.org`. A GitHub-mirror workaround was attempted and rejected. You (the user) were offered options and explicitly chose:

> "Plain PHP/Blade mockup, drop Livewire" — skip Composer entirely, hand-build a minimal PHP router + Blade-like templating shim, use Alpine.js instead of Livewire for interactivity.

Everything in this repo follows from that decision.

## What was built instead

A small, dependency-free PHP micro-framework designed to *look and feel* like Laravel conventions so migrating to real Laravel later is mechanical, not a rewrite:

- **`App\Routing\Route`** — static `Route::get($uri, $action)->name($name)` API, `{param}` placeholders, closures or `[Class, 'method']` callables. Mirrors Laravel's `Route` facade closely enough that `routes/web.php` reads like a real Laravel routes file.
- **`App\View\BladeLite`** — a from-scratch compiler that supports the actual Blade syntax used throughout this app: `@extends`, `@section`/`@endsection`, `@yield`, `@include`, `@if`/`@elseif`/`@else`/`@endif`, `@foreach`/`@endforeach`, `@forelse`/`@empty`/`@endforelse`, `@php`/`@endphp`, `{{ }}` (escaped) and `{!! !!}` (raw) echoes, and anonymous component tags (`<x-stat-card :value="$x" />`) with isolated variable scope and named slots. Compiled templates are cached to `storage/framework/views/` and only recompiled when the source `.blade.php` file's mtime changes — the same caching model Laravel uses.
- **Alpine.js** in place of Livewire for all client-side state: dropdowns, modals, tabs, the mobile drawer, the attendance status-cycling buttons, and the notifications filter all use `x-data`/`x-on`/`x-show`/`x-model` rather than server round-trips.
- **Fully vendored, zero-CDN assets**: Tailwind CSS (compiled via the Tailwind CLI, not the Play CDN), Alpine.js, Chart.js, lucide icons (as static SVG partials), and the Cairo/IBM Plex Mono fonts are all copied from `node_modules` into `public/build/`, `public/fonts/`, and `public/icons/` by `scripts/sync-vendor-assets.mjs`. The running app makes zero network calls — this matters both for this sandbox and for demoing the app anywhere without internet access.

## What's genuinely out of scope (by design, per the original brief)

Per the brief's explicit "do not implement" list for Phase 1, none of the following exist anywhere in this codebase, even though some page UIs (forms, "add" buttons, dropdown actions) visually suggest them:

- Real authentication / sessions / password handling
- A database, migrations, or any Eloquent-style models
- Real CRUD — every "Add", "Edit", "Delete" action shows a toast and changes nothing
- Multi-tenancy / tenant isolation
- Roles & permissions enforcement
- Any HTTP API
- Payment processing integration

All data on every page comes from `app/Support/Mock/*.php`, generated deterministically (index-based cycling, not `rand()`/`time()`), so the UI is stable and reviewable across reloads.

## Migrating to real Laravel + Livewire (Phase 2)

When Composer access is available (i.e., outside this sandbox, on your own machine or CI):

1. `composer create-project laravel/laravel planzeen-v2` and `composer require livewire/livewire`.
2. Move `resources/views/**` almost as-is — the Blade syntax used here (`@extends`, `@section`, `@foreach`, `{{ }}`, component tags) is standard Blade and will parse unmodified in real Laravel. The only things to double check: anonymous component slot edge cases and any place a `@php` block relies on this repo's specific helper functions.
3. Re-implement `app/helpers.php` functions (`mad()`, `ar_date()`, `initials()`, `is_active_route()`) as either global helpers (`app/helpers.php` autoloaded via `composer.json` `"files"`) or a Blade directive/service — they're plain PHP with no framework coupling, so this is a copy-paste.
4. Replace `App\Support\Mock\*` classes with real Eloquent models + migrations (Student, Teacher, Course, Group, Enrollment, AttendanceRecord, ScheduleSlot, Payment, Expense, SalaryPayment, Notification) — the mock classes' method names and return shapes (arrays of associative arrays) were deliberately kept close to what a `Model::all()->toArray()`/resource-collection call would return, to make this swap mostly mechanical.
5. Convert the highest-interactivity pieces (attendance status-cycling, notifications filtering, the search/filter bars, modals that will need real submit handlers) to Livewire components — Alpine's `x-data` state maps fairly directly onto Livewire component public properties, and Alpine can still be layered on top of Livewire (Livewire ships with Alpine already) for purely presentational interactivity like dropdowns/tabs.
6. Add real auth (Laravel Breeze/Fortify or Jetstream), multi-tenancy (a package like `stancl/tenancy` or a simple `team_id` scoping approach), and policies/permissions (Laravel's built-in `Gate`/`Policy` or Spatie's permission package) — none of this exists yet by design.
7. Re-point `routes/web.php` at real controllers instead of closures once controllers exist.
8. `npm run build` already produces the exact same compiled Tailwind CSS / vendored JS / icons / fonts setup real Laravel expects in `public/build` — no changes needed there beyond wiring up Vite (or keeping the Tailwind CLI build if preferred) in the new project's `package.json`.

## Known minor items

- `public/favicon.ico` was an empty placeholder file in the original Laravel skeleton; it's been replaced with a minimal generated icon (or removed — see repo state) so the browser tab doesn't request a 0-byte file.
- `storage/framework/views/*.php` (compiled Blade-lite cache) is regenerated automatically on first request; it's fine to delete the directory contents at any time.
- This is a sandbox-only workaround, not a recommendation — for any real Phase 2 work, use real Laravel + Livewire as originally specified. This repo exists solely so the Phase 1 UI could be built and verified end-to-end without Composer/network access.
