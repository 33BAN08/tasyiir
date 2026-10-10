<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const STATUSES = ['مكتمل', 'جزئي', 'غير مؤدي'];

    /** Subscription packs a center can sell. 1 = the original monthly billing. */
    public const DURATIONS = [1, 3, 6, 12];

    /** Enrollment payment status → Student financial_status. */
    public const FINANCIAL_STATUS_MAP = [
        'مكتمل' => 'مؤدي',
        'جزئي' => 'جزئي',
        'غير مؤدي' => 'غير مؤدي',
    ];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'course_id',
        'group_id',
        'date',
        'due_date',
        'duration_months',
        'price',
        'discount',
        'remaining',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'duration_months' => 'integer',
        ];
    }

    /** Months covered by one period of this enrollment; never 0. */
    public function getMonthsAttribute(): int
    {
        $months = (int) ($this->duration_months ?: 1);

        return in_array($months, self::DURATIONS, true) ? $months : 1;
    }

    /** Arabic label for a pack length, translated at display time like every other stored value. */
    public static function durationLabel(int $months): string
    {
        return match ($months) {
            3 => 'ثلاثة أشهر',
            6 => 'ستة أشهر',
            12 => 'سنوي',
            default => 'شهري',
        };
    }

    public function getDurationLabelAttribute(): string
    {
        return self::durationLabel($this->months);
    }

    /** End of the period currently being billed — the due date itself. */
    public function getPeriodEndAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->due_date?->copy();
    }

    /** Start of that period: one pack length before it ends. */
    public function getPeriodStartAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->due_date?->copy()->subMonthsNoOverflow($this->months);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->lt(today())
            && $this->status !== 'مكتمل';
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', 'مكتمل');
    }

    /**
     * Period rollover: a fully paid enrollment whose due date has passed owes
     * the next period, so it becomes unpaid again (full net amount outstanding)
     * and the student's financial badge follows. With packs the outstanding
     * amount is the pack price, because `price` holds what one period costs —
     * a 6-month pack owes six months' worth, not one.
     *
     * The due date deliberately stays put until the new period is settled: that
     * is what makes the enrollment show as overdue (see getIsOverdueAttribute)
     * and feeds the "متأخرون عن الأداء" counters. Settling it in full is what
     * moves the deadline forward by one pack length — see applyPayment().
     *
     * Idempotent and cheap, so it is run lazily on page load.
     */
    public static function rolloverDue(): int
    {
        $due = static::with('student')
            ->where('status', 'مكتمل')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today())
            ->get();

        foreach ($due as $enrollment) {
            $enrollment->forceFill([
                'status' => 'غير مؤدي',
                'remaining' => $enrollment->net,
            ])->save();
            $enrollment->syncStudent();
        }

        return $due->count();
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function getNetAttribute(): int
    {
        return max(0, (int) $this->price - (int) $this->discount);
    }

    public function getPaidAttribute(): int
    {
        return max(0, $this->net - (int) $this->remaining);
    }

    /** Derive remaining + status from what was charged and what was paid. */
    public static function settle(int $price, int $discount, int $paid): array
    {
        $net = max(0, $price - $discount);
        $paid = min(max(0, $paid), $net);
        $remaining = $net - $paid;

        return [
            'remaining' => $remaining,
            'status' => match (true) {
                $remaining === 0 => 'مكتمل',
                $paid > 0 => 'جزئي',
                default => 'غير مؤدي',
            },
        ];
    }

    /**
     * Record money received against this enrollment: lower the balance, derive
     * the status, and push the result onto the student's financial badge.
     */
    public function applyPayment(int $amount): void
    {
        $paid = $this->paid + max(0, $amount);
        $settled = self::settle((int) $this->price, (int) $this->discount, $paid);

        // Settling a due/overdue period in full moves the deadline forward by one
        // pack length (a month for monthly enrollments) — otherwise the rollover
        // would flip it straight back to unpaid.
        if ($settled['status'] === 'مكتمل' && $this->status !== 'مكتمل' && $this->due_date && $this->due_date->lte(today())) {
            $settled['due_date'] = $this->due_date->copy()->addMonthsNoOverflow($this->months);
        }

        $this->forceFill($settled)->save();
        $this->syncStudent();
    }

    /** Enrollment is the source of truth for the financial side of a registration. */
    public function syncStudent(): void
    {
        $student = $this->student;
        if (! $student) {
            return;
        }

        $student->course_id = $this->course_id;
        $student->group_id = $this->group_id;
        $student->financial_status = self::FINANCIAL_STATUS_MAP[$this->status] ?? $student->financial_status;
        $student->save();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->whereHas('student', fn (Builder $s) => $s->where('name', 'like', "%{$term}%"));
    }

    public function scopeCourseFilter(Builder $query, ?int $courseId): Builder
    {
        return $courseId ? $query->where('course_id', $courseId) : $query;
    }

    public function scopeStatusFilter(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
