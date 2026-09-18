<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'gender',
        'phone',
        'email',
        'city',
        'guardian_phone',
        'course_id',
        'group_id',
        'registered_at',
        'enrollment_status',
        'financial_status',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'date',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)->latestOfMany('date');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeEnrollmentStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('enrollment_status', $status) : $query;
    }

    public function scopeFinancialStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('financial_status', $status) : $query;
    }

    public function scopeCourse(Builder $query, ?int $courseId): Builder
    {
        return $courseId ? $query->where('course_id', $courseId) : $query;
    }
}
