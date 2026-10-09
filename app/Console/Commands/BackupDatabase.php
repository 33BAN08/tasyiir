<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackup;
use Illuminate\Console\Command;

/**
 * Called by start.bat when the center switches the PC on, and available by
 * hand. The app also takes one automatically on the first page of each day.
 */
class BackupDatabase extends Command
{
    protected $signature = 'tasyiir:backup {--force : Take a snapshot even if today already has one}';

    protected $description = 'Take a consistent SQLite snapshot of this installation (keeps the last 30)';

    public function handle(DatabaseBackup $backup): int
    {
        if (! $backup->supported()) {
            $this->warn('Database backups apply to SQLite installations only — nothing to do.');

            return self::SUCCESS;
        }

        if (! $backup->isWritable()) {
            $this->error('Backup folder is not writable: '.$backup->path());

            return self::FAILURE;
        }

        $last = $backup->lastBackupAt();

        if (! $this->option('force') && $last && $last->isToday()) {
            $this->info('A backup already exists for today ('.$last->format('Y-m-d H:i').').');

            return self::SUCCESS;
        }

        $name = $backup->run();

        $this->info('Backup written: '.$backup->path().DIRECTORY_SEPARATOR.$name);

        return self::SUCCESS;
    }
}
