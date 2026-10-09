<?php

namespace App\Http\Controllers;

use App\Exports\BackupExport;
use App\Exports\StudentImportTemplateExport;
use App\Services\DatabaseBackup;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    /** Full Excel backup of the signed-in user's center, one sheet per entity. */
    public function download()
    {
        $center = Str::slug(auth()->user()->tenant?->name ?? 'center', '-', 'ar') ?: 'center';

        return Excel::download(new BackupExport, "tasyiir-backup-{$center}-".now()->format('Y-m-d_Hi').'.xlsx');
    }

    /** Empty student-import template (header row only). */
    public function studentTemplate()
    {
        return Excel::download(new StudentImportTemplateExport, 'tasyiir-students-template.xlsx');
    }

    /**
     * Download one database snapshot. The name is matched against the listing
     * rather than used as a path, so it cannot escape the backup folder.
     */
    public function downloadDatabase(string $name): BinaryFileResponse
    {
        $file = app(DatabaseBackup::class)->files()->firstWhere('name', $name);

        abort_unless($file, 404);

        return response()->download($file['path'], $file['name']);
    }
}
