<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A center's signup request. Deliberately NOT tenant-scoped: it is platform
 * data that exists before the tenant does, and only platform admins read it.
 */
class CenterSignupRequest extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public const STATUS_LABELS = [
        'pending' => 'قيد المراجعة',
        'approved' => 'مقبول',
        'rejected' => 'مرفوض',
    ];

    protected $fillable = [
        'center_name',
        'owner_name',
        'owner_email',
        'owner_phone',
        'password',
        'status',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /** The platform admin who approved/rejected it — tenant-less, so read past the tenant scope. */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withoutGlobalScope(TenantScope::class);
    }

    /** The tenant created on approval. */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
