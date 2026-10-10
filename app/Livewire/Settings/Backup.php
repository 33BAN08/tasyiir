<?php

namespace App\Livewire\Settings;

use App\Imports\StudentsImport;
use App\Services\DatabaseBackup;
use App\Support\Mode;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Settings → النسخ الاحتياطي: database snapshots (the real backup — it can
 * restore a center), the Excel export (readable, but cannot restore), and the
 * student importer.
 */
class Backup extends Component
{
    use WithFileUploads;

    /** Typed by the owner before a restore is allowed. */
    public const RESTORE_CONFIRMATION = 'استعادة';

    public $file;

    public ?int $imported = null;

    /** Enrollments created by rows that filled the optional duration column. */
    public int $enrolled = 0;

    /** @var list<array{row: int, message: string}> */
    public array $failures = [];

    // Database backups
    public string $backupPath = '';

    public ?string $restoreName = null;

    public $restoreFile;

    public string $restoreConfirmation = '';

    public function mount(): void
    {
        $this->authorizeBackup();
        $this->backupPath = app(DatabaseBackup::class)->path();
    }

    /** Owner-level only: a full data export/import is not for every staff account. */
    protected function authorizeBackup(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
    }

    // ─── Database snapshots ────────────────────────────────────────────────

    public function savePath(): void
    {
        $this->authorizeBackup();

        $this->validate(
            ['backupPath' => ['required', 'string', 'max:255']],
            [],
            ['backupPath' => __('مجلد النسخ الاحتياطي')],
        );

        $service = app(DatabaseBackup::class);
        $service->setPath($this->backupPath);

        if (! $service->isWritable()) {
            $this->addError('backupPath', __('تعذر الكتابة في هذا المجلد. تحقق من المسار ومن صلاحيات الكتابة.'));

            return;
        }

        $this->dispatch('toast', message: __('تم حفظ مجلد النسخ الاحتياطي'));
    }

    public function backupNow(): void
    {
        $this->authorizeBackup();

        $service = app(DatabaseBackup::class);

        if (! $service->supported()) {
            $this->dispatch('toast', message: __('النسخ الاحتياطي لقاعدة البيانات متاح على قواعد SQLite فقط.'));

            return;
        }

        try {
            $name = $service->run();
        } catch (\Throwable $e) {
            $this->addError('backupPath', __('فشل إنشاء النسخة: :error', ['error' => $e->getMessage()]));

            return;
        }

        $this->dispatch('toast', message: __('تم إنشاء النسخة :name', ['name' => $name]));
    }

    // ─── Restore (local edition, owner only) ───────────────────────────────

    public function restore(): void
    {
        $this->authorizeBackup();
        abort_unless(Mode::isLocal(), 403);

        $this->validate([
            'restoreConfirmation' => ['required', 'string'],
            'restoreFile' => ['nullable', 'file', 'max:102400'],
        ], [], [
            'restoreConfirmation' => __('كلمة التأكيد'),
            'restoreFile' => __('ملف النسخة'),
        ]);

        if (trim($this->restoreConfirmation) !== self::RESTORE_CONFIRMATION) {
            $this->addError('restoreConfirmation', __('اكتب كلمة :word بالضبط للتأكيد.', ['word' => self::RESTORE_CONFIRMATION]));

            return;
        }

        $service = app(DatabaseBackup::class);

        $source = $this->restoreFile
            ? $this->restoreFile->getRealPath()
            : collect($service->files())->firstWhere('name', $this->restoreName)['path'] ?? null;

        if (! $source) {
            $this->addError('restoreName', __('اختر نسخة احتياطية أو ارفع ملفاً.'));

            return;
        }

        if (! $service->isValidBackup($source)) {
            $this->addError('restoreName', __('هذا الملف ليس نسخة احتياطية صالحة من TASYIIR.'));

            return;
        }

        if (! $service->restore($source)) {
            $this->addError('restoreName', __('تعذرت الاستعادة. تم الاحتفاظ بقاعدة البيانات الحالية.'));

            return;
        }

        // The restored file brings its own sessions table; sign out cleanly.
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('status', __('تمت استعادة النسخة الاحتياطية بنجاح. سجّل الدخول من جديد.'));

        $this->redirect(route('login'), navigate: false);
    }

    // ─── Student import ────────────────────────────────────────────────────

    public function import(): void
    {
        $this->authorizeBackup();

        $this->validate(
            ['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240']],
            [],
            ['file' => __('ملف الاستيراد')],
        );

        $import = new StudentsImport;
        Excel::import($import, $this->file->getRealPath(), null, \Maatwebsite\Excel\Excel::XLSX);
        // Rows that filled the duration column get their enrollment now that
        // the students have been saved and have ids.
        $import->createPendingEnrollments();

        $this->imported = $import->imported;
        $this->enrolled = $import->enrolled;
        $this->failures = $import->failures;
        $this->reset('file');

        $this->dispatch('toast', message: __('تم استيراد :count طالباً', ['count' => $this->imported]));
    }

    public function clearResult(): void
    {
        $this->reset(['imported', 'enrolled', 'failures', 'file']);
    }

    public function render()
    {
        $service = app(DatabaseBackup::class);

        return view('livewire.settings.backup', [
            'headings' => StudentsImport::HEADINGS,
            'dbSupported' => $service->supported(),
            'backups' => $service->supported() ? $service->files(DatabaseBackup::AUTO_PREFIX)->take(10) : collect(),
            'lastBackupAt' => $service->supported() ? $service->lastBackupAt() : null,
            'writable' => $service->isWritable(),
            'isLocal' => Mode::isLocal(),
            'confirmWord' => self::RESTORE_CONFIRMATION,
        ]);
    }
}
