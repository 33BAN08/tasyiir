<?php

namespace App\Exports\Sheets;

use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;

class GroupsSheet extends ModelSheet
{
    public function title(): string
    {
        return 'Groups';
    }

    protected function query(): Builder
    {
        return Group::with(['course', 'teacher'])->orderBy('id');
    }

    protected function columns(): array
    {
        return [
            'ID' => 'id',
            'الاسم' => 'name',
            'الدورة' => fn ($g) => $g->course?->name,
            'الأستاذ' => fn ($g) => $g->teacher?->name,
            'السعة' => 'capacity',
            'القاعة' => 'room',
            'التوقيت' => 'schedule',
            'الحالة' => 'status',
        ];
    }
}
