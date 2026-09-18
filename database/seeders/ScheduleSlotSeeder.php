<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\ScheduleSlot;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

/**
 * One or two weekly slots per seeded group, derived from the group's own
 * `schedule` text ("الاثنين والأربعاء 16:00 - 17:30" → Monday + Wednesday at
 * 16:00 - 17:30), so the grid, the group cards and attendance dates all agree.
 */
class ScheduleSlotSeeder extends Seeder
{
    public static function parse(?string $schedule): array
    {
        if (! $schedule || ! preg_match('/(\d{1,2}:\d{2}\s*-\s*\d{1,2}:\d{2})/u', $schedule, $m)) {
            return ['days' => [], 'time' => null];
        }

        $time = preg_replace('/\s*-\s*/u', ' - ', $m[1]);
        $days = array_values(array_filter(ScheduleSlot::DAYS, fn ($d) => str_contains($schedule, $d)));

        return ['days' => $days, 'time' => $time];
    }

    public function run(): void
    {
        Tenant::all()->each(function (Tenant $tenant) {
            Group::withoutGlobalScopes()->where('tenant_id', $tenant->id)->orderBy('id')->get()
                ->each(function (Group $group) use ($tenant) {
                    ['days' => $days, 'time' => $time] = self::parse($group->schedule);

                    foreach ($days as $day) {
                        ScheduleSlot::create([
                            'tenant_id' => $tenant->id,
                            'day' => $day,
                            'time' => $time,
                            'course_id' => $group->course_id,
                            'group_id' => $group->id,
                            'teacher_id' => $group->teacher_id,
                            'room' => $group->room,
                        ]);
                    }
                });
        });
    }
}
