<?php

namespace App\Exports\Sheets;

use App\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\Builder;

class AttendanceSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Attendance';
    }

    protected function query(): Builder
    {
        return AttendanceRecord::with(['student', 'group'])->orderBy('date')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الطالب' => fn ($a) => $a->student?->name,
            'المجموعة' => fn ($a) => $a->group?->name,
            'التاريخ' => 'date',
            'الحالة' => 'state',
        ];
    }
}
