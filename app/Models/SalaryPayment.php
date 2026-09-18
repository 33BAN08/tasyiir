<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One running balance per teacher (the table has no period column, so this is
 * NOT monthly payroll history). `salary` is recomputed from the teacher's
 * salary structure on every load; `paid` is the running total actually paid
 * out and is only ever changed by recordPayment().
 */
class SalaryPayment extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'teacher_id',
        'hours',
        'rate',
        'salary',
        'paid',
        'remaining',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Expected salary for a teacher right now:
     *  - ثابت: the fixed monthly amount (rate is informational: salary / hours).
     *  - بالعمولة: commission_rate % of this month's payments from the teacher's
     *    students (via Teacher::students(), the hasManyThrough over groups).
     */
    public static function computeFor(Teacher $teacher): array
    {
        $hours = (int) $teacher->hours;

        if ($teacher->isCommissionBased()) {
            $studentIds = $teacher->students()->pluck('students.id');
            $revenue = (int) Payment::withoutGlobalScopes()
                ->where('tenant_id', $teacher->tenant_id)
                ->whereIn('student_id', $studentIds)
                ->whereMonth('date', now()->month)->whereYear('date', now()->year)
                ->sum('amount');

            return [
                'hours' => $hours,
                'rate' => null,
                'salary' => (int) round(((int) $teacher->commission_rate) / 100 * $revenue),
            ];
        }

        $salary = (int) $teacher->fixed_salary;

        return [
            'hours' => $hours,
            'rate' => $hours > 0 ? (int) round($salary / $hours) : null,
            'salary' => $salary,
        ];
    }

    /** Upsert the teacher's balance row from the current computation, keeping `paid` untouched. */
    public static function refreshFor(Teacher $teacher): self
    {
        $row = static::withoutGlobalScopes()->firstOrNew(['teacher_id' => $teacher->id]);
        $row->tenant_id = $teacher->tenant_id;
        $row->fill(static::computeFor($teacher));
        $row->paid = (int) $row->paid;
        $row->settle();

        return $row;
    }

    /** Derive remaining + status from salary and paid, then save. */
    public function settle(): void
    {
        $this->paid = min((int) $this->paid, (int) $this->salary);
        $this->remaining = max((int) $this->salary - $this->paid, 0);
        $this->status = match (true) {
            $this->paid > 0 && $this->remaining === 0 => 'مدفوع',
            $this->paid > 0 => 'مدفوع جزئياً',
            default => 'غير مدفوع',
        };
        $this->save();
    }

    public function recordPayment(int $amount): void
    {
        $this->paid = (int) $this->paid + max(0, $amount);
        $this->settle();
    }
}
