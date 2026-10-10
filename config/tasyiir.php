<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Edition
    |--------------------------------------------------------------------------
    |
    | "local"  — one center, installed on that center's own Windows PC. The
    |            owner creates their account through the first-run wizard at
    |            /setup; there is no public signup, no platform back office and
    |            licence checks apply.
    | "saas"   — the hosted multi-tenant edition: /register-center and the
    |            platform admin at /admin are available and licences are
    |            skipped entirely.
    |
    | The default is "local" on purpose: if a client install ever loses its
    | .env, it fails closed to the offline single-center mode instead of
    | exposing public signup and the operator's back office.
    |
    */

    'mode' => env('TASYIIR_MODE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | SaaS signup
    |--------------------------------------------------------------------------
    |
    | false — /register-center provisions the center immediately and signs the
    |         owner in. true — it files a request a platform admin reviews at
    |         /admin (the original flow). Ignored in local mode.
    |
    */

    'signup_requires_approval' => env('TASYIIR_SIGNUP_REQUIRES_APPROVAL', false),

    /*
    |--------------------------------------------------------------------------
    | Online demo (hosted edition only)
    |--------------------------------------------------------------------------
    |
    | A public trial server: anyone can register a center at /register-center
    | and use it for `days` days, optionally pre-filled with sample data. When
    | the trial ends the modules stop opening and the owner is shown a
    | "contact us on WhatsApp" page. Ignored in local mode.
    |
    */

    'demo' => [
        'enabled' => (bool) env('TASYIIR_DEMO', false),
        'days' => (int) env('TASYIIR_DEMO_DAYS', 7),
        // International format without "+" or spaces, e.g. 212612345678.
        'whatsapp' => preg_replace('/\D+/', '', (string) env('TASYIIR_DEMO_WHATSAPP', '')),
        'price' => env('TASYIIR_DEMO_PRICE', '500 DH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted proxies
    |--------------------------------------------------------------------------
    |
    | Set to "*" when the app is reached through a reverse proxy or tunnel
    | (Cloudflare Tunnel, nginx) so HTTPS links and visitor IPs are correct.
    | Leave empty for a plain local install.
    |
    */

    'trusted_proxies' => env('TASYIIR_TRUSTED_PROXIES'),

    /*
    |--------------------------------------------------------------------------
    | Database backups (local edition)
    |--------------------------------------------------------------------------
    |
    | The client's PC is the only copy of their data. Backups are SQLite
    | "VACUUM INTO" snapshots — never a raw copy of a file the app may be
    | writing. The folder can be changed by the owner in Settings and should
    | point at a second drive, a USB key or a synced cloud folder.
    |
    */

    'backup' => [
        'path' => env('TASYIIR_BACKUP_PATH', storage_path('backups')),
        'keep' => (int) env('TASYIIR_BACKUP_KEEP', 30),
        'stale_after_days' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Licence (local edition)
    |--------------------------------------------------------------------------
    |
    | Ed25519 public key (base64) of the key pair generated once by the
    | vendor with tools/license-issuer/keygen.php. Only the PUBLIC half ever
    | ships. Empty means "unsigned build": every install stays on trial and
    | no licence can be activated, which is a safe default for a repo that
    | must not contain anyone's real key.
    |
    */

    'license' => [
        'public_key' => env('TASYIIR_LICENSE_PUBLIC_KEY', ''),
        'file' => env('TASYIIR_LICENSE_FILE', storage_path('licence.key')),
        'trial_days' => (int) env('TASYIIR_TRIAL_DAYS', 14),
        'warn_before_days' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Release version
    |--------------------------------------------------------------------------
    |
    | Stamped into the Windows release zip and shown in Settings → الترخيص.
    |
    */

    'version' => env('TASYIIR_VERSION', '1.1.0'),

];
