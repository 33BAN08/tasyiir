<?php

namespace App\Livewire\Settings;

use App\Imports\StudentsImport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Settings → النسخ الاحتياطي: download the full backup / the student template
 * (plain GET routes) and upload a filled template to import students.
 */
class Backup extends Component
{
    use WithFileUploads;

    public $file;

    public ?int $imported = null;

    /** @var list<array{row: int, message: string}> */
    public array $failures = [];

    public function mount(): void
    {
        $this->authorizeBackup();
    }

    /** Owner-level only: a full data export/import is not for every staff account. */
    protected function authorizeBackup(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);
    }

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

        $this->imported = $import->imported;
        $this->failures = $import->failures;
        $this->reset('file');

        $this->dispatch('toast', message: __('تم استيراد :count طالباً', ['count' => $this->imported]));
    }

    public function clearResult(): void
    {
        $this->reset(['imported', 'failures', 'file']);
    }

    public function render()
    {
        return view('livewire.settings.backup', ['headings' => StudentsImport::HEADINGS]);
    }
}
