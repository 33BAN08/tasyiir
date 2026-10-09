<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Tenant;
use App\Services\CenterProvisioner;
use App\Services\DatabaseBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/**
 * Backups run against a real SQLite file (VACUUM INTO cannot snapshot the
 * :memory: database the rest of the suite uses), so this case builds its own
 * database instead of RefreshDatabase.
 */
class DatabaseBackupTest extends TestCase
{
    use RunsInMode;

    protected string $dbPath;

    protected string $backupPath;

    protected function setUp(): void
    {
        $this->runInMode('local');

        parent::setUp();

        $this->dbPath = storage_path('framework/testing/backup-test-'.getmypid().'.sqlite');
        $this->backupPath = storage_path('framework/testing/backups-'.getmypid());

        File::ensureDirectoryExists(dirname($this->dbPath));
        File::delete($this->dbPath);
        File::deleteDirectory($this->backupPath);
        File::put($this->dbPath, '');

        config([
            'tasyiir.mode' => 'local',
            'tasyiir.backup.path' => $this->backupPath,
            'tasyiir.backup.keep' => 3,
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->dbPath,
        ]);

        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->run();
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        DB::disconnect();
        File::delete($this->dbPath);
        File::deleteDirectory($this->backupPath);

        parent::tearDown();
    }

    public function test_it_writes_a_restorable_snapshot(): void
    {
        $backup = app(DatabaseBackup::class);
        $this->assertTrue($backup->supported());

        $name = $backup->run();

        $this->assertNotNull($name);
        $this->assertFileExists($this->backupPath.DIRECTORY_SEPARATOR.$name);
        $this->assertTrue($backup->isValidBackup($this->backupPath.DIRECTORY_SEPARATOR.$name));
        $this->assertTrue($backup->lastBackupAt()->isToday());
    }

    public function test_it_keeps_only_the_configured_number_of_snapshots(): void
    {
        $backup = app(DatabaseBackup::class);

        // Backups are named per minute, so fake five different ones.
        foreach (range(1, 5) as $i) {
            $this->travelTo(now()->addMinutes($i));
            $backup->run();
        }
        $this->travelBack();

        $this->assertCount(3, $backup->files(DatabaseBackup::AUTO_PREFIX));
    }

    public function test_the_daily_backup_runs_once_a_day(): void
    {
        $backup = app(DatabaseBackup::class);

        $backup->runDailyIfDue();
        $this->travelTo(now()->addMinutes(5));
        $backup->runDailyIfDue();

        $this->assertCount(1, $backup->files(DatabaseBackup::AUTO_PREFIX));

        $this->travelTo(now()->addDay());
        $backup->runDailyIfDue();
        $this->travelBack();

        $this->assertCount(2, $backup->files(DatabaseBackup::AUTO_PREFIX));
    }

    public function test_a_stale_or_unwritable_folder_is_reported(): void
    {
        $backup = app(DatabaseBackup::class);

        $this->assertTrue($backup->isStale(), 'no backup yet counts as stale');

        $backup->run();
        $this->assertFalse($backup->isStale());

        $this->travelTo(now()->addDays(4));
        $this->assertTrue($backup->isStale());
        $this->travelBack();
    }

    public function test_restoring_a_snapshot_brings_the_old_data_back(): void
    {
        $backup = app(DatabaseBackup::class);

        ['tenant' => $tenant, 'owner' => $owner] = app(CenterProvisioner::class)
            ->provision('مركز الاختبار', 'المدير', 'owner@test.test', 'secret1234');
        $this->actingAs($owner);

        Student::create(['tenant_id' => $tenant->id, 'name' => 'طالب قبل النسخة', 'phone' => '0600000000',
            'registered_at' => now()->toDateString(), 'enrollment_status' => 'نشط', 'financial_status' => 'غير مؤدي']);

        $name = $backup->run();

        Student::create(['tenant_id' => $tenant->id, 'name' => 'طالب بعد النسخة', 'phone' => '0611111111',
            'registered_at' => now()->toDateString(), 'enrollment_status' => 'نشط', 'financial_status' => 'غير مؤدي']);
        $this->assertSame(2, Student::withoutGlobalScopes()->count());

        $this->assertTrue($backup->restore($this->backupPath.DIRECTORY_SEPARATOR.$name));

        $this->assertSame(1, Student::withoutGlobalScopes()->count());
        $this->assertSame('طالب قبل النسخة', Student::withoutGlobalScopes()->sole()->name);
        // A safety copy of the pre-restore state is kept.
        $this->assertCount(1, $backup->files(DatabaseBackup::SAFETY_PREFIX));
    }

    public function test_it_refuses_anything_that_is_not_a_tasyiir_database(): void
    {
        $backup = app(DatabaseBackup::class);
        $before = Tenant::count();

        $garbage = $this->backupPath.DIRECTORY_SEPARATOR.'garbage.sqlite';
        File::ensureDirectoryExists($this->backupPath);
        File::put($garbage, str_repeat('not a database ', 500));

        $emptyDb = $this->backupPath.DIRECTORY_SEPARATOR.'empty.sqlite';
        new \PDO('sqlite:'.$emptyDb);

        $this->assertFalse($backup->isValidBackup($garbage));
        $this->assertFalse($backup->isValidBackup($emptyDb));
        $this->assertFalse($backup->isValidBackup($this->backupPath.DIRECTORY_SEPARATOR.'missing.sqlite'));

        $this->assertFalse($backup->restore($garbage));
        $this->assertSame($before, Tenant::count(), 'a rejected file must not touch the live database');
    }
}
