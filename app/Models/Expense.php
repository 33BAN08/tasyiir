<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const CATEGORIES = ['الكراء', 'الكهرباء', 'الإنترنت', 'المعدات', 'التسويق', 'المستلزمات', 'الصيانة', 'أخرى'];

    /** Icon + tone per category, as the Phase 1 tiles showed them. */
    public const CATEGORY_META = [
        'الكراء' => ['icon' => 'building-2', 'tone' => 'blue'],
        'الكهرباء' => ['icon' => 'zap', 'tone' => 'amber'],
        'الإنترنت' => ['icon' => 'globe', 'tone' => 'violet'],
        'المعدات' => ['icon' => 'package', 'tone' => 'rose'],
        'التسويق' => ['icon' => 'megaphone', 'tone' => 'brand'],
        'المستلزمات' => ['icon' => 'notebook-pen', 'tone' => 'blue'],
        'الصيانة' => ['icon' => 'wrench', 'tone' => 'amber'],
        'أخرى' => ['icon' => 'more-horizontal', 'tone' => 'violet'],
    ];

    public const METHODS = ['نقداً', 'تحويل بنكي', 'بطاقة بنكية', 'شيك'];

    protected $fillable = [
        'tenant_id',
        'category',
        'label',
        'amount',
        'date',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $term ? $query->where('label', 'like', "%{$term}%") : $query;
    }

    public function scopeCategoryFilter(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('category', $category) : $query;
    }

    public function scopeMethodFilter(Builder $query, ?string $method): Builder
    {
        return $method ? $query->where('method', $method) : $query;
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('date', now()->month)->whereYear('date', now()->year);
    }
}
