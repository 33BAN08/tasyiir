<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const METHODS = ['نقداً', 'تحويل بنكي', 'بطاقة بنكية', 'شيك'];

    public const STATUSES = ['مؤدي بالكامل', 'دفعة جزئية'];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'amount',
        'method',
        'date',
        'status',
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

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->whereHas('student', fn (Builder $s) => $s->where('name', 'like', "%{$term}%"));
    }

    public function scopeMethodFilter(Builder $query, ?string $method): Builder
    {
        return $method ? $query->where('method', $method) : $query;
    }

    public function scopeStatusFilter(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
