<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * SQLite backups for the local edition, where the client's PC is the only
 * copy of their data.
 *
 * Snapshots are taken with "VACUUM INTO", which writes a consistent copy of a
 * database that is in use — a plain file copy of a live SQLite file can catch
 * it mid-write and produce a file that will not open.
 */
class DatabaseBackup
{
    public const AUTO_PREFIX = 'tasyiir-';

    public const SAFETY_PREFIX = 'pre-restore-';

    /** Tables a file must contain before we accept it as a TASYIIR database. */
    public const REQUIRED_TABLES = ['tenants', 'users', 'students', 'enrollments', 'migrations'];

    public function supported(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite' && $this->databasePath() !== null;
    }

    public function databasePath(): ?string
    {
        $path = DB::connection()->getDatabaseName();

        return is_string($path) && $path !== ':memory:' && $path !== '' ? $path : null;
    }

    /** Owner-configurable (Settings → النسخ الاحتياطي); defaults to storage/backups. */
    public function path(): string
    {
        $configured = Tenant::query()->oldest('id')->value('settings');
        $configured = is_array($configured) ? ($configured['backup_path'] ?? null) : null;

        $path = is_string($configured) && trim($configured) !== '' ? trim($configured) : config('tasyiir.backup.path');

        return rtrim((string) $path, "\\/");
    }

    public function setPath(?string $path): void
    {
        $tenant = Tenant::query()->oldest('id')->first();

        if (! $tenant) {
            return;
        }

        $tenant->settings = array_merge($tenant->settings ?? [], [
            'backup_path' => $path && trim($path) !== '' ? rtrim(trim($path), "\\/") : null,
        ]);
        $tenant->save();
    }

    public function isWritable(): bool
    {
        $path = $this->path();

        try {
            File::ensureDirectoryExists($path);
        } catch (\Throwable) {
            return false;
        }

        return is_dir($path) && is_writable($path);
    }

    /**
     * Take a snapshot now. Returns the file name, or null when backups do not
     * apply (non-SQLite) — never throws into a request.
     */
    public function run(string $prefix = self::AUTO_PREFIX): ?string
    {
        if (! $this->supported()) {
            return null;
        }

        File::ensureDirectoryExists($this->path());

        $name = $prefix.now()->format('Y-m-d_Hi').'.sqlite';
        $target = $this->path().DIRECTORY_SEPARATOR.$name;

        // VACUUM INTO refuses to overwrite; a second backup in the same minute
        // just reuses the existing one.
        if (is_file($target)) {
            return $name;
        }

        DB::statement('VACUUM INTO '.DB::connection()->getPdo()->quote($target));

        $this->rotate();

        return $name;
    }

    /** Keep the most recent automatic backups (default 30) and a few safety copies. */
    public function rotate(): void
    {
        foreach ([[self::AUTO_PREFIX, (int) config('tasyiir.backup.keep', 30)], [self::SAFETY_PREFIX, 5]] as [$prefix, $keep]) {
            $this->files($prefix)->slice($keep)->each(fn (array $file) => File::delete($file['path']));
        }
    }

    /**
     * Backups newest first.
     *
     * @return Collection<int, array{name: string, path: string, size: int, created_at: Carbon}>
     */
    public function files(?string $prefix = null): Collection
    {
        if (! is_dir($this->path())) {
            return collect();
        }

        return collect(File::glob($this->path().DIRECTORY_SEPARATOR.($prefix ?? '*').'*.sqlite'))
            ->map(fn (string $path) => [
                'name' => basename($path),
                'path' => $path,
                'size' => (int) @filesize($path),
                'created_at' => Carbon::createFromTimestamp((int) @filemtime($path)),
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    public function lastBackupAt(): ?Carbon
    {
        return $this->files()->first()['created_at'] ?? null;
    }

    /** No backup in the last few days, or the folder cannot be written to. */
    public function isStale(): bool
    {
        if (! $this->supported()) {
            return false;
        }

        if (! $this->isWritable()) {
            return true;
        }

        $last = $this->lastBackupAt();

        return $last === null || $last->lt(now()->subDays((int) config('tasyiir.backup.stale_after_days', 3)));
    }

    /**
     * Once a day, on the first request that needs it — the same lazy pattern
     * as the enrollments rollover, so a local install needs no cron or Task
     * Scheduler. Never lets a backup problem break the page.
     */
    public function runDailyIfDue(): void
    {
        if (! $this->supported()) {
            return;
        }

        $last = $this->lastBackupAt();

        if ($last && $last->isToday()) {
            return;
        }

        try {
            $this->run();
        } catch (\Throwable $e) {
            Log::warning('Automatic backup failed: '.$e->getMessage());
        }
    }

    /** A file is only accepted if SQLite can open it and it holds our tables. */
    public function isValidBackup(string $path): bool
    {
        if (! is_file($path) || filesize($path) < 1024) {
            return false;
        }

        try {
            $pdo = new \PDO('sqlite:'.$path, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable) {
            return false;
        }

        return empty(array_diff(self::REQUIRED_TABLES, $tables));
    }

    /**
     * Replace the live database with a backup: safety copy first, then swap,
     * then drop the compiled caches (the restored file carries its own
     * sessions table, so everyone is signed out).
     *
     * @return bool false when the file is not a valid TASYIIR database
     */
    public function restore(string $sourcePath): bool
    {
        if (! $this->supported() || ! $this->isValidBackup($sourcePath)) {
            return false;
        }

        $this->run(self::SAFETY_PREFIX);

        $live = $this->databasePath();

        DB::disconnect();

        File::copy($sourcePath, $live, true);

        try {
            Artisan::call('config:clear');
            Artisan::call('view:clear');
        } catch (\Throwable) {
            // Caches are rebuilt on the next request anyway.
        }

        // A backup taken before an update carries that version's schema. The
        // code running now is newer, so bring the restored database up to it —
        // otherwise the center comes back to missing-column errors.
        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable) {
            // Reported by the health banner; the data itself is already in place.
        }

        return true;
    }
}
