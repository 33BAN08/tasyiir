<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Group;
use App\Models\Tenant;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * Minimal real Teachers/Courses/Groups rows — reusing Phase 1's mock catalog
 * — so Students (the real module this phase) can hold genuine course_id /
 * group_id foreign keys. The Teachers/Courses/Groups modules themselves stay
 * on Phase 1 mock data until Phase 3; these rows exist purely as lookup data.
 */
class ReferenceDataSeeder extends Seeder
{
    protected array $teacherNames = [
        ['name' => 'أستاذ يوسف الفاسي', 'specialty' => 'اللغة الإنجليزية'],
        ['name' => 'أستاذة سلمى برادة', 'specialty' => 'اللغة الإنجليزية'],
        ['name' => 'أستاذ كريم العلوي', 'specialty' => 'اللغة الفرنسية'],
        ['name' => 'أستاذة نور الإدريسي', 'specialty' => 'اللغة الفرنسية'],
        ['name' => 'أستاذ عادل بنموسى', 'specialty' => 'الرياضيات'],
        ['name' => 'أستاذ رشيد الوردي', 'specialty' => 'الإعلاميات', 'status' => 'في إجازة'],
        ['name' => 'أستاذ طارق المنصوري', 'specialty' => 'IELTS / TOEFL'],
        ['name' => 'أستاذة مريم الغازي', 'specialty' => 'المحاسبة', 'status' => 'متوقف'],
    ];

    protected array $courseNames = [
        ['name' => 'English A1', 'level' => 'مبتدئ', 'price' => 900],
        ['name' => 'English B1', 'level' => 'متوسط', 'price' => 1000],
        ['name' => 'Français A1', 'level' => 'مبتدئ', 'price' => 900],
        ['name' => 'Français B1', 'level' => 'متوسط', 'price' => 1000],
        ['name' => 'Mathématiques', 'level' => 'الثانوي', 'price' => 700],
        ['name' => 'Informatique', 'level' => 'مبتدئ إلى متوسط', 'price' => 850],
        ['name' => 'IELTS', 'level' => 'متقدم', 'price' => 1500, 'status' => 'جديد'],
        ['name' => 'المحاسبة والتدبير', 'level' => 'متوسط', 'price' => 1200, 'status' => 'متوقف مؤقتاً'],
    ];

    protected array $rooms = ['القاعة 1', 'القاعة 2', 'القاعة 3', 'القاعة 4', 'القاعة 5', 'قاعة الحاسوب'];

    protected array $schedules = [
        'الاثنين والأربعاء 16:00 - 17:30',
        'الثلاثاء والخميس 14:00 - 15:30',
        'السبت 10:00 - 12:00',
        'الأحد 12:00 - 14:00',
        'الجمعة 18:00 - 19:30',
    ];

    public function run(): void
    {
        Tenant::all()->each(function (Tenant $tenant) {
            $teachers = collect($this->teacherNames)->map(fn ($t, $i) => Teacher::create([
                'tenant_id' => $tenant->id,
                'name' => $t['name'],
                'specialty' => $t['specialty'],
                'phone' => sprintf('06%02d-%02d-%02d-%02d', 60 + $i, 10 + $i, 20 + $i, 30 + $i),
                'email' => 'teacher'.($i + 1).'@'.$tenant->slug.'.test',
                'hours' => 20 + ($i * 4),
                // Alternate fixed / commission so both salary structures are visible.
                'salary_type' => $i % 2 === 0 ? 'ثابت' : 'بالعمولة',
                'fixed_salary' => $i % 2 === 0 ? 3500 + ($i * 250) : null,
                'commission_rate' => $i % 2 === 0 ? null : 30 + ($i * 5),
                'status' => $t['status'] ?? 'نشط',
            ]));

            $courses = collect($this->courseNames)->map(fn ($c, $i) => Course::create([
                'tenant_id' => $tenant->id,
                'name' => $c['name'],
                'level' => $c['level'],
                'teacher_id' => $teachers[$i % $teachers->count()]->id,
                'price' => $c['price'],
                'status' => $c['status'] ?? 'نشط',
            ]));

            foreach ($courses as $i => $course) {
                foreach (['A', 'B'] as $j => $letter) {
                    // k = 2i + j walks 0..15; (k % 5, k / 5) gives every group a distinct
                    // (schedule, room) pair, and the two groups of one course (same teacher)
                    // always get different schedules — so seeded data has no room or
                    // teacher double-bookings.
                    $k = 2 * $i + $j;

                    Group::create([
                        'tenant_id' => $tenant->id,
                        'name' => $course->name.' - '.$letter,
                        'course_id' => $course->id,
                        'teacher_id' => $course->teacher_id,
                        'capacity' => 18,
                        'room' => $this->rooms[intdiv($k, count($this->schedules)) % count($this->rooms)],
                        'schedule' => $this->schedules[$k % count($this->schedules)],
                        'status' => $course->status,
                    ]);
                }
            }
        });
    }
}
