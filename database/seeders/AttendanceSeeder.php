<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Group;
use App\Models\ScheduleSlot;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Attendance for the last 8 sessions of every group, on the weekdays that
 * group actually meets (from its schedule slots), with roughly Phase 1's
 * 3:1:1 حاضر / متأخر / غائب distribution.
 */
class AttendanceSeeder extends Seeder
{
    protected array $states = ['حاضر', 'حاضر', 'حاضر', 'متأخر', 'غائب'];

    protected array $dayIndex = ['الاثنين' => 1, 'الثلاثاء' => 2, 'الأربعاء' => 3, 'الخميس' => 4, 'الجمعة' => 5, 'السبت' => 6, 'الأحد' => 7];

    public function run(): void
    {
        Tenant::all()->each(function (Tenant $tenant) {
            $groups = Group::withoutGlobalScopes()->where('tenant_id', $tenant->id)->with('students')->get();

            foreach ($groups as $group) {
                $weekdays = ScheduleSlot::withoutGlobalScopes()->where('group_id', $group->id)->pluck('day')
                    ->map(fn ($d) => $this->dayIndex[$d] ?? null)->filter()->unique()->values();

                if ($weekdays->isEmpty()) {
                    $weekdays = collect([1]);
                }

                $dates = $this->lastSessions($weekdays, 8);

                foreach ($group->students as $si => $student) {
                    foreach ($dates as $di => $date) {
                        AttendanceRecord::create([
                            'tenant_id' => $tenant->id,
                            'student_id' => $student->id,
                            'group_id' => $group->id,
                            'date' => $date,
                            'state' => $this->states[($student->id + $di) % count($this->states)],
                        ]);
                    }
                }
            }
        });
    }

    /** Walk back from yesterday collecting the last N dates that fall on the group's weekdays. */
    protected function lastSessions($weekdays, int $count): array
    {
        $dates = [];
        $cursor = Carbon::yesterday();

        while (count($dates) < $count) {
            if ($weekdays->contains($cursor->isoWeekday())) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->subDay();
        }

        return $dates;
    }
}
