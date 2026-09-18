<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleSlot extends Model
{
    use BelongsToTenant;

    /** Week order used by the schedule grid (same as Phase 1's Mock\Schedule::DAYS). */
    public const DAYS = ['الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'];

    public const ROOMS = ['القاعة 1', 'القاعة 2', 'القاعة 3', 'القاعة 4', 'القاعة 5', 'قاعة الحاسوب'];

    protected $fillable = [
        'tenant_id',
        'day',
        'time',
        'course_id',
        'group_id',
        'teacher_id',
        'room',
    ];

    /** "HH:MM - HH:MM" → [startMinutes, endMinutes] since midnight. */
    public static function parseTimeRange(string $time): array
    {
        if (! preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/u', $time, $m)) {
            return [0, 0];
        }

        return [(int) $m[1] * 60 + (int) $m[2], (int) $m[3] * 60 + (int) $m[4]];
    }

    /** Half-open intervals [s1,e1) and [s2,e2) overlap when s1 < e2 and s2 < e1. */
    public static function rangesOverlap(array $a, array $b): bool
    {
        return $a[0] < $b[1] && $b[0] < $a[1];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function scopeTeacherFilter(Builder $query, ?int $teacherId): Builder
    {
        return $teacherId ? $query->where('teacher_id', $teacherId) : $query;
    }

    public function scopeRoomFilter(Builder $query, ?string $room): Builder
    {
        return $room ? $query->where('room', $room) : $query;
    }
}
