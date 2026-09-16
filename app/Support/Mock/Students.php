<?php

namespace App\Support\Mock;

class Students
{
    protected const MALE_FIRST = ['يوسف', 'محمد', 'أحمد', 'عمر', 'أنس', 'سفيان', 'إلياس', 'عبد الرحمن', 'هشام', 'سعيد', 'طارق', 'رضا', 'أمين', 'بلال', 'زكرياء', 'ياسين', 'عادل', 'نبيل', 'كريم', 'حمزة'];
    protected const FEMALE_FIRST = ['فاطمة', 'خديجة', 'مريم', 'سلمى', 'لمياء', 'نور', 'إيمان', 'هدى', 'سارة', 'ياسمين', 'أسماء', 'زينب', 'حنان', 'دنيا', 'رجاء', 'وفاء', 'آية', 'بشرى', 'كوثر', 'منال'];
    protected const LAST = ['العلوي', 'الفاسي', 'بنعلي', 'الإدريسي', 'الحسني', 'بنموسى', 'الشرقاوي', 'الوردي', 'زروالي', 'المنصوري', 'بوزيان', 'الغازي', 'الجابري', 'الوهابي', 'الصبار', 'التازي', 'السباعي', 'القادري', 'بناني', 'الخياطي', 'الزيتوني', 'المرابط', 'السلاوي', 'الطاهري'];

    protected const ENROLLMENT_STATUSES = ['نشط', 'نشط', 'نشط', 'نشط', 'متوقف'];
    protected const FINANCIAL_STATUSES = ['مؤدي', 'مؤدي', 'مؤدي', 'جزئي', 'غير مؤدي'];

    public static function all(): array
    {
        static $students = null;
        if ($students !== null) {
            return $students;
        }

        $courses = Courses::all();
        $groups = Groups::all();
        $count = 54;
        $students = [];

        for ($i = 0; $i < $count; $i++) {
            $isMale = $i % 2 === 0;
            $first = $isMale ? self::MALE_FIRST[$i % count(self::MALE_FIRST)] : self::FEMALE_FIRST[$i % count(self::FEMALE_FIRST)];
            $last = self::LAST[($i * 7 + 3) % count(self::LAST)];
            $name = $first . ' ' . $last;

            $group = $groups[$i % count($groups)];
            $course = self::courseByName($courses, $group['course']);

            $day = 1 + ($i % 27);
            $month = 1 + ($i % 9); // registrations spread over the current academic period
            $regDate = sprintf('2026-%02d-%02d', $month, $day);

            $phonePrefix = $i % 3 === 0 ? '06' : '07';
            $phone = sprintf('%s%02d-%02d-%02d-%02d', $phonePrefix, 10 + ($i % 89), 10 + (($i * 3) % 89), 10 + (($i * 5) % 89), 10 + (($i * 7) % 89));

            $students[] = [
                'id' => $i + 1,
                'name' => $name,
                'gender' => $isMale ? 'male' : 'female',
                'phone' => $phone,
                'email' => self::slug($name) . ($i + 1) . '@gmail.com',
                'course' => $group['course'],
                'course_id' => $course['id'] ?? null,
                'group' => $group['name'],
                'group_id' => $group['id'],
                'registered_at' => $regDate,
                'enrollment_status' => self::ENROLLMENT_STATUSES[$i % count(self::ENROLLMENT_STATUSES)],
                'financial_status' => self::FINANCIAL_STATUSES[$i % count(self::FINANCIAL_STATUSES)],
                'city' => self::cityFor($i),
                'guardian_phone' => sprintf('06%02d-%02d-%02d-%02d', 20 + ($i % 79), 10 + (($i * 2) % 89), 10 + (($i * 4) % 89), 10 + (($i * 6) % 89)),
            ];
        }

        return $students;
    }

    public static function find(int $id): ?array
    {
        foreach (static::all() as $s) {
            if ($s['id'] === $id) {
                return $s;
            }
        }
        return null;
    }

    public static function stats(): array
    {
        $all = static::all();
        return [
            'total' => 250, // headline figure per product spec
            'active' => 214,
            'paused' => 36,
            'unpaid' => 18,
        ];
    }

    protected static function courseByName(array $courses, string $name): ?array
    {
        foreach ($courses as $c) {
            if ($c['name'] === $name) {
                return $c;
            }
        }
        return null;
    }

    protected static function cityFor(int $i): string
    {
        $cities = ['الدار البيضاء', 'الرباط', 'مراكش', 'فاس', 'طنجة', 'أكادير', 'مكناس'];
        return $cities[$i % count($cities)];
    }

    protected static function slug(string $name): string
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
