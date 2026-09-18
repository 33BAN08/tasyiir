<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use BelongsToTenant;

    public const STATES = ['حاضر', 'متأخر', 'غائب'];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'group_id',
        'date',
        'state',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeForGroupOnDate(Builder $query, int $groupId, string $date): Builder
    {
        return $query->where('group_id', $groupId)->whereDate('date', $date);
    }
}
