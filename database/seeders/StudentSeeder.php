<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

/**
 * Realistic Moroccan student data, reusing Phase 1's name/city/phone
 * generation approach (deterministic, not random) so re-seeding is stable.
 */
class StudentSeeder extends Seeder
{
    protected const MALE_FIRST = ['يوسف', 'محمد', 'أحمد', 'عمر', 'أنس', 'سفيان', 'إلياس', 'عبد الرحمن', 'هشام', 'سعيد', 'طارق', 'رضا', 'أمين', 'بلال', 'زكرياء', 'ياسين', 'عادل', 'نبيل', 'كريم', 'حمزة'];
    protected const FEMALE_FIRST = ['فاطمة', 'خديجة', 'مريم', 'سلمى', 'لمياء', 'نور', 'إيمان', 'هدى', 'سارة', 'ياسمين', 'أسماء', 'زينب', 'حنان', 'دنيا', 'رجاء', 'وفاء', 'آية', 'بشرى', 'كوثر', 'منال'];
    protected const LAST = ['العلوي', 'الفاسي', 'بنعلي', 'الإدريسي', 'الحسني', 'بنموسى', 'الشرقاوي', 'الوردي', 'زروالي', 'المنصوري', 'بوزيان', 'الغازي', 'الجابري', 'الوهابي', 'الصبار', 'التازي', 'السباعي', 'القادري', 'بناني', 'الخياطي', 'الزيتوني', 'المرابط', 'السلاوي', 'الطاهري'];
    protected const CITIES = ['الدار البيضاء', 'الرباط', 'مراكش', 'فاس', 'طنجة', 'أكادير', 'مكناس'];
    protected const ENROLLMENT_STATUSES = ['نشط', 'نشط', 'نشط', 'نشط', 'متوقف'];
    protected const FINANCIAL_STATUSES = ['مؤدي', 'مؤدي', 'مؤدي', 'جزئي', 'غير مؤدي'];

    protected int $perTenant = 45;

    public function run(): void
    {
        Tenant::all()->each(function (Tenant $tenant) {
            $groups = Group::where('tenant_id', $tenant->id)->get();

            for ($i = 0; $i < $this->perTenant; $i++) {
                $isMale = $i % 2 === 0;
                $first = $isMale
                    ? self::MALE_FIRST[$i % count(self::MALE_FIRST)]
                    : self::FEMALE_FIRST[$i % count(self::FEMALE_FIRST)];
                $last = self::LAST[($i * 7 + 3 + $tenant->id) % count(self::LAST)];
                $name = $first.' '.$last;

                $group = $groups[$i % $groups->count()];

                $day = 1 + ($i % 27);
                $month = 1 + ($i % 9);
                // Never register someone in the future (September days past today).
                $registeredAt = min(sprintf('2026-%02d-%02d', $month, $day), now()->toDateString());

                $phonePrefix = $i % 3 === 0 ? '06' : '07';
                $phone = sprintf('%s%02d-%02d-%02d-%02d', $phonePrefix, 10 + ($i % 89), 10 + (($i * 3) % 89), 10 + (($i * 5) % 89), 10 + (($i * 7) % 89));

                Student::create([
                    'tenant_id' => $tenant->id,
                    'name' => $name,
                    'gender' => $isMale ? 'male' : 'female',
                    'phone' => $phone,
                    'email' => $this->slug($name).($i + 1).'@gmail.com',
                    'city' => self::CITIES[$i % count(self::CITIES)],
                    'guardian_phone' => sprintf('06%02d-%02d-%02d-%02d', 20 + ($i % 79), 10 + (($i * 2) % 89), 10 + (($i * 4) % 89), 10 + (($i * 6) % 89)),
                    'course_id' => $group->course_id,
                    'group_id' => $group->id,
                    'registered_at' => $registeredAt,
                    'enrollment_status' => self::ENROLLMENT_STATUSES[$i % count(self::ENROLLMENT_STATUSES)],
                    'financial_status' => self::FINANCIAL_STATUSES[$i % count(self::FINANCIAL_STATUSES)],
                ]);
            }
        });
    }

    protected function slug(string $name): string
    {
        $map = [
            'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j',
            'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh', 'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh',
            'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q',
            'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'y',
            'ة' => 'a', 'ء' => '', ' ' => '.',
        ];
        return strtolower(strtr($name, $map));
    }
}
