<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App-level notification feed (custom table, not Laravel's Notifiable system).
 * user_id NULL = tenant-wide (everyone in the tenant sees it); set = that user only.
 */
class Notification extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'body',
        'icon',
        'tone',
        'category',
        'read',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Create a notification for the current tenant (tenant-wide unless user_id is given). */
    public static function notify(array $attrs): self
    {
        return static::create($attrs + [
            'tenant_id' => auth()->user()?->tenant_id,
            'tone' => 'brand',
            'read' => false,
        ]);
    }

    /** Tenant-wide notifications plus the ones addressed to this user. */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->whereNull('user_id');
            if ($user) {
                $q->orWhere('user_id', $user->id);
            }
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('read', false);
    }
}
