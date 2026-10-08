<?php

namespace App\Exports;

use App\Imports\StudentsImport;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** The empty template a center fills in to import its student list: header row only. */
class StudentImportTemplateExport implements ShouldAutoSize, WithHeadings
{
    public function headings(): array
    {
        return StudentsImport::HEADINGS;
    }
}
