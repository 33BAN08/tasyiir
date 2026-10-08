<?php

namespace App\Http\Controllers;

use App\Exports\BackupExport;
use App\Exports\StudentImportTemplateExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BackupController extends Controller
{
    /** Full backup of the signed-in user's center, one sheet per entity. */
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
}
