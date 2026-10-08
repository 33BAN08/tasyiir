<?php

namespace App\Exports\Sheets;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;

class CoursesSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Courses';
    }

    protected function query(): Builder
    {
        return Course::with('teacher')->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الاسم' => 'name',
            'المستوى' => 'level',
            'الأستاذ' => fn ($c) => $c->teacher?->name,
            'السعر' => 'price',
            'الحالة' => 'status',
        ];
    }
}
