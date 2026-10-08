<?php

namespace App\Exports\Sheets;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentsSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Enrollments';
    }

    protected function query(): Builder
    {
        return Enrollment::with(['student', 'course', 'group'])->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الطالب' => fn ($e) => $e->student?->name,
            'الدورة' => fn ($e) => $e->course?->name,
            'المجموعة' => fn ($e) => $e->group?->name,
            'تاريخ التسجيل' => 'date',
            'تاريخ الاستحقاق' => 'due_date',
            'السعر' => 'price',
            'الخصم' => 'discount',
            'المتبقي' => 'remaining',
            'الحالة' => 'status',
        ];
    }
}
