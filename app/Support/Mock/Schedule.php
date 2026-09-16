<?php

namespace App\Support\Mock;

class Schedule
{
    public const DAYS = ['الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'];

    public static function week(): array
    {
        static $week = null;
        if ($week !== null) {
            return $week;
        }

        $week = [
            'الاثنين' => [
                ['time' => '14:00 - 15:30', 'course' => 'Français A1', 'group' => 'Français A1 - A', 'teacher' => 'أستاذ كريم العلوي', 'room' => 'القاعة 4'],
                ['time' => '16:00 - 17:30', 'course' => 'English A1', 'group' => 'English A1 - A', 'teacher' => 'أستاذ يوسف الفاسي', 'room' => 'القاعة 1'],
                ['time' => '17:30 - 19:00', 'course' => 'Mathématiques', 'group' => 'Mathématiques - A', 'teacher' => 'أستاذ عادل بنموسى', 'room' => 'القاعة 5'],
                ['time' => '18:00 - 19:30', 'course' => 'English A2', 'group' => 'English A2 - A', 'teacher' => 'أستاذة سلمى برادة', 'room' => 'القاعة 1'],
                ['time' => '19:30 - 21:00', 'course' => 'TOEFL', 'group' => 'TOEFL - A', 'teacher' => 'أستاذة ليلى الحسني', 'room' => 'القاعة 3'],
            ],
            'الثلاثاء' => [
                ['time' => '14:00 - 15:30', 'course' => 'Informatique', 'group' => 'Informatique - A', 'teacher' => 'أستاذ رشيد الوردي', 'room' => 'قاعة الحاسوب'],
                ['time' => '16:00 - 17:30', 'course' => 'English B1', 'group' => 'English B1 - A', 'teacher' => 'أستاذ يوسف الفاسي', 'room' => 'القاعة 2'],
                ['time' => '18:00 - 19:30', 'course' => 'Français B1', 'group' => 'Français B1 - A', 'teacher' => 'أستاذة نور الإدريسي', 'room' => 'القاعة 2'],
            ],
            'الأربعاء' => [
                ['time' => '14:00 - 15:30', 'course' => 'Français A1', 'group' => 'Français A1 - A', 'teacher' => 'أستاذ كريم العلوي', 'room' => 'القاعة 4'],
                ['time' => '16:00 - 17:30', 'course' => 'English A1', 'group' => 'English A1 - A', 'teacher' => 'أستاذ يوسف الفاسي', 'room' => 'القاعة 1'],
                ['time' => '17:30 - 19:00', 'course' => 'Mathématiques', 'group' => 'Mathématiques - A', 'teacher' => 'أستاذ عادل بنموسى', 'room' => 'القاعة 5'],
                ['time' => '18:00 - 19:30', 'course' => 'English A2', 'group' => 'English A2 - A', 'teacher' => 'أستاذة سلمى برادة', 'room' => 'القاعة 1'],
                ['time' => '19:30 - 21:00', 'course' => 'IELTS', 'group' => 'IELTS - B', 'teacher' => 'أستاذ طارق المنصوري', 'room' => 'القاعة 1'],
            ],
            'الخميس' => [
                ['time' => '14:00 - 15:30', 'course' => 'Informatique', 'group' => 'Informatique - A', 'teacher' => 'أستاذ رشيد الوردي', 'room' => 'قاعة الحاسوب'],
                ['time' => '16:00 - 17:30', 'course' => 'English B1', 'group' => 'English B1 - A', 'teacher' => 'أستاذ يوسف الفاسي', 'room' => 'القاعة 2'],
                ['time' => '18:00 - 19:30', 'course' => 'Français B1', 'group' => 'Français B1 - A', 'teacher' => 'أستاذة نور الإدريسي', 'room' => 'القاعة 2'],
                ['time' => '18:00 - 19:30', 'course' => 'Espagnol A1', 'group' => 'Espagnol A1 - A', 'teacher' => 'أستاذ سعيد بوزيان', 'room' => 'القاعة 4'],
            ],
            'الجمعة' => [
                ['time' => '16:00 - 17:30', 'course' => 'Français A1', 'group' => 'Français A1 - B', 'teacher' => 'أستاذة أسماء الوهابي', 'room' => 'القاعة 4'],
                ['time' => '18:00 - 19:30', 'course' => 'Mathématiques', 'group' => 'Mathématiques - B', 'teacher' => 'أستاذ يونس الصبار', 'room' => 'القاعة 5'],
            ],
            'السبت' => [
                ['time' => '09:00 - 11:00', 'course' => 'المحاسبة والتدبير', 'group' => 'المحاسبة - A', 'teacher' => 'أستاذة مريم الغازي', 'room' => 'القاعة 5'],
                ['time' => '10:00 - 12:00', 'course' => 'English A2', 'group' => 'English A2 - B', 'teacher' => 'أستاذة سلمى برادة', 'room' => 'القاعة 3'],
                ['time' => '12:00 - 14:00', 'course' => 'English B1', 'group' => 'English B1 - B', 'teacher' => 'أستاذة سلمى برادة', 'room' => 'القاعة 1'],
                ['time' => '14:00 - 16:00', 'course' => 'Français B1', 'group' => 'Français B1 - B', 'teacher' => 'أستاذة نور الإدريسي', 'room' => 'القاعة 4'],
                ['time' => '16:00 - 17:30', 'course' => 'Informatique', 'group' => 'Informatique - B', 'teacher' => 'أستاذة حنان زروالي', 'room' => 'قاعة الحاسوب'],
            ],
            'الأحد' => [
                ['time' => '09:00 - 11:00', 'course' => 'المحاسبة والتدبير', 'group' => 'المحاسبة - B', 'teacher' => 'أستاذة مريم الغازي', 'room' => 'القاعة 5'],
                ['time' => '10:00 - 12:00', 'course' => 'English B2', 'group' => 'English B2 - A', 'teacher' => 'أستاذة سلمى برادة', 'room' => 'القاعة 3'],
                ['time' => '12:00 - 14:00', 'course' => 'IELTS', 'group' => 'IELTS - A', 'teacher' => 'أستاذ طارق المنصوري', 'room' => 'القاعة 1'],
            ],
        ];

        return $week;
    }

    /** Flat, time-sorted list of today's remaining classes — used on the dashboard. */
    public static function upcoming(int $limit = 4): array
    {
        $today = self::DAYS[(int) (new \DateTime('now'))->format('N') - 1] ?? self::DAYS[0];
        $classes = self::week()[$today] ?? self::week()[self::DAYS[0]];
        return array_slice($classes, 0, $limit);
    }
}
