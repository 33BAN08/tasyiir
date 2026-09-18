<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'specialty',
        'phone',
        'email',
        'hours',
        'salary_type',
        'fixed_salary',
        'commission_rate',
        'status',
    ];

    public const SALARY_TYPES = ['ثابت', 'بالعمولة'];

    public function isCommissionBased(): bool
    {
        return $this->salary_type === 'بالعمولة';
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, Group::class, 'teacher_id', 'group_id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('specialty', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeSpecialty(Builder $query, ?string $specialty): Builder
    {
        return $specialty ? $query->where('specialty', $specialty) : $query;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
