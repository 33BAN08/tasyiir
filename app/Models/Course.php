<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'level',
        'teacher_id',
        'price',
        'price_3_months',
        'price_6_months',
        'price_12_months',
        'status',
    ];

    /** Pack duration → the column holding its special price, when the center set one. */
    public const PACK_PRICE_COLUMNS = [
        3 => 'price_3_months',
        6 => 'price_6_months',
        12 => 'price_12_months',
    ];

    /**
     * What this course costs for a subscription of $months. A center that did
     * not set a pack price is simply billed the monthly price × months, which
     * is also what every course did before packs existed.
     */
    public function priceFor(int $months): int
    {
        $column = self::PACK_PRICE_COLUMNS[$months] ?? null;

        if ($column !== null && $this->{$column} !== null) {
            return (int) $this->{$column};
        }

        return (int) $this->price * max(1, $months);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where('name', 'like', "%{$term}%");
    }

    public function scopeLevel(Builder $query, ?string $level): Builder
    {
        return $level ? $query->where('level', $level) : $query;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
