<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use BelongsToTenant;

    public const STATES = ['حاضر', 'متأخر', 'غائب'];

    /** The states a parent is warned about. */
    public const NOTIFIABLE_STATES = ['غائب', 'متأخر'];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'group_id',
        'date',
        'state',
        'notified_at',
        'notified_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'notified_at' => 'datetime',
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

    public function notifier()
    {
        return $this->belongsTo(User::class, 'notified_by');
    }

    public function isNotifiable(): bool
    {
        return in_array($this->state, self::NOTIFIABLE_STATES, true);
    }

    public function scopeNotifiable(Builder $query): Builder
    {
        return $query->whereIn('state', self::NOTIFIABLE_STATES);
    }

    public function scopeNotNotified(Builder $query): Builder
    {
        return $query->whereNull('notified_at');
    }

    /**
     * The number the message goes to: the parent's, or the student's own as a
     * fallback so a center is never stuck — the UI says which one it is.
     *
     * @return array{phone: ?string, isGuardian: bool}
     */
    public function notifyPhone(): array
    {
        $guardian = $this->student?->guardian_phone;

        if (\App\Support\WhatsApp::isValid($guardian)) {
            return ['phone' => $guardian, 'isGuardian' => true];
        }

        return ['phone' => $this->student?->phone, 'isGuardian' => false];
    }

    public function scopeForGroupOnDate(Builder $query, int $groupId, string $date): Builder
    {
        return $query->where('group_id', $groupId)->whereDate('date', $date);
    }
}
