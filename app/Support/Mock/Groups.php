<?php

namespace App\Support\Mock;

class Groups
{
    public static function all(): array
    {
        static $groups = null;
        if ($groups !== null) {
            return $groups;
        }

        $raw = [
            ['name' => 'English A1 - A', 'course' => 'English A1', 'teacher' => 'أستاذ يوسف الفاسي', 'students' => 18, 'capacity' => 20, 'room' => 'القاعة 1', 'schedule' => 'الاثنين والأربعاء 16:00 - 17:30', 'status' => 'نشط'],
            ['name' => 'English A1 - B', 'course' => 'English A1', 'teacher' => 'أستاذ يوسف الفاسي', 'students' => 16, 'capacity' => 20, 'room' => 'القاعة 2', 'schedule' => 'الثلاثاء والخميس 14:00 - 15:30', 'status' => 'نشط'],
            ['name' => 'English A2 - A', 'course' => 'English A2', 'teacher' => 'أستاذة سلمى برادة', 'students' => 15, 'capacity' => 18, 'room' => 'القاعة 1', 'schedule' => 'الاثنين والأربعاء 18:00 - 19:30', 'status' => 'نشط'],
            ['name' => 'English A2 - B', 'course' => 'English A2', 'teacher' => 'أستاذة سلمى برادة', 'students' => 14, 'capacity' => 18, 'room' => 'القاعة 3', 'schedule' => 'السبت 10:00 - 12:00', 'status' => 'نشط'],
            ['name' => 'English B1 - A', 'course' => 'English B1', 'teacher' => 'أستاذ يوسف الفاسي', 'students' => 17, 'capacity' => 18, 'room' => 'القاعة 2', 'schedule' => 'الثلاثاء والخميس 16:00 - 17:30', 'status' => 'نشط'],
            ['name' => 'English B1 - B', 'course' => 'English B1', 'teacher' => 'أستاذة سلمى برادة', 'students' => 14, 'capacity' => 18, 'room' => 'القاعة 1', 'schedule' => 'السبت 12:00 - 14:00', 'status' => 'نشط'],
            ['name' => 'English B2 - A', 'course' => 'English B2', 'teacher' => 'أستاذة سلمى برادة', 'students' => 14, 'capacity' => 16, 'room' => 'القاعة 3', 'schedule' => 'الأحد 10:00 - 12:00', 'status' => 'نشط'],
            ['name' => 'Français A1 - A', 'course' => 'Français A1', 'teacher' => 'أستاذ كريم العلوي', 'students' => 15, 'capacity' => 18, 'room' => 'القاعة 4', 'schedule' => 'الاثنين والأربعاء 14:00 - 15:30', 'status' => 'نشط'],
            ['name' => 'Français A1 - B', 'course' => 'Français A1', 'teacher' => 'أستاذة أسماء الوهابي', 'students' => 12, 'capacity' => 18, 'room' => 'القاعة 4', 'schedule' => 'الجمعة 16:00 - 17:30', 'status' => 'نشط'],
            ['name' => 'Français B1 - A', 'course' => 'Français B1', 'teacher' => 'أستاذة نور الإدريسي', 'students' => 13, 'capacity' => 16, 'room' => 'القاعة 2', 'schedule' => 'الثلاثاء والخميس 18:00 - 19:30', 'status' => 'نشط'],
            ['name' => 'Français B1 - B', 'course' => 'Français B1', 'teacher' => 'أستاذة نور الإدريسي', 'students' => 11, 'capacity' => 16, 'room' => 'القاعة 4', 'schedule' => 'السبت 14:00 - 16:00', 'status' => 'نشط'],
            ['name' => 'Mathématiques - A', 'course' => 'Mathématiques', 'teacher' => 'أستاذ عادل بنموسى', 'students' => 16, 'capacity' => 20, 'room' => 'القاعة 5', 'schedule' => 'الاثنين والأربعاء 17:30 - 19:00', 'status' => 'نشط'],
            ['name' => 'Mathématiques - B', 'course' => 'Mathématiques', 'teacher' => 'أستاذ يونس الصبار', 'students' => 13, 'capacity' => 20, 'room' => 'القاعة 5', 'schedule' => 'الجمعة 18:00 - 19:30', 'status' => 'نشط'],
            ['name' => 'Informatique - A', 'course' => 'Informatique', 'teacher' => 'أستاذ رشيد الوردي', 'students' => 14, 'capacity' => 16, 'room' => 'قاعة الحاسوب', 'schedule' => 'الثلاثاء والخميس 14:00 - 15:30', 'status' => 'نشط'],
            ['name' => 'Informatique - B', 'course' => 'Informatique', 'teacher' => 'أستاذة حنان زروالي', 'students' => 12, 'capacity' => 16, 'room' => 'قاعة الحاسوب', 'schedule' => 'السبت 16:00 - 17:30', 'status' => 'نشط'],
            ['name' => 'IELTS - A', 'course' => 'IELTS', 'teacher' => 'أستاذ طارق المنصوري', 'students' => 12, 'capacity' => 14, 'room' => 'القاعة 1', 'schedule' => 'الأحد 12:00 - 14:00', 'status' => 'نشط'],
            ['name' => 'IELTS - B', 'course' => 'IELTS', 'teacher' => 'أستاذ طارق المنصوري', 'students' => 10, 'capacity' => 14, 'room' => 'القاعة 1', 'schedule' => 'الأربعاء 19:30 - 21:00', 'status' => 'نشط'],
            ['name' => 'TOEFL - A', 'course' => 'TOEFL', 'teacher' => 'أستاذة ليلى الحسني', 'students' => 11, 'capacity' => 14, 'room' => 'القاعة 3', 'schedule' => 'الاثنين 19:30 - 21:00', 'status' => 'نشط'],
            ['name' => 'Espagnol A1 - A', 'course' => 'Espagnol A1', 'teacher' => 'أستاذ سعيد بوزيان', 'students' => 15, 'capacity' => 18, 'room' => 'القاعة 4', 'schedule' => 'الخميس 18:00 - 19:30', 'status' => 'جديد'],
            ['name' => 'المحاسبة - A', 'course' => 'المحاسبة والتدبير', 'teacher' => 'أستاذة مريم الغازي', 'students' => 14, 'capacity' => 18, 'room' => 'القاعة 5', 'schedule' => 'السبت 09:00 - 11:00', 'status' => 'متوقف مؤقتاً'],
            ['name' => 'المحاسبة - B', 'course' => 'المحاسبة والتدبير', 'teacher' => 'أستاذة مريم الغازي', 'students' => 13, 'capacity' => 18, 'room' => 'القاعة 5', 'schedule' => 'الأحد 09:00 - 11:00', 'status' => 'متوقف مؤقتاً'],
        ];

        $groups = [];
        foreach ($raw as $i => $g) {
            $g['id'] = $i + 1;
            $groups[] = $g;
        }

        return $groups;
    }

    public static function find(int $id): ?array
    {
        foreach (static::all() as $g) {
            if ($g['id'] === $id) {
                return $g;
            }
        }
        return null;
    }

    public static function names(): array
    {
        return array_column(static::all(), 'name');
    }
}
