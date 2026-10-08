<?php

namespace App\Exports;

use App\Exports\Sheets\AttendanceSheet;
use App\Exports\Sheets\CoursesSheet;
use App\Exports\Sheets\EnrollmentsSheet;
use App\Exports\Sheets\ExpensesSheet;
use App\Exports\Sheets\GroupsSheet;
use App\Exports\Sheets\PaymentsSheet;
use App\Exports\Sheets\SalariesSheet;
use App\Exports\Sheets\StudentsSheet;
use App\Exports\Sheets\TeachersSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Full read-only backup of the signed-in user's center: one sheet per entity.
 * Every sheet's query runs under TenantScope, so no other center's rows can
 * appear (and nothing here calls withoutGlobalScopes()).
 */
class BackupExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new StudentsSheet,
            new TeachersSheet,
            new CoursesSheet,
            new GroupsSheet,
            new EnrollmentsSheet,
            new PaymentsSheet,
            new AttendanceSheet,
            new ExpensesSheet,
            new SalariesSheet,
        ];
    }
}
