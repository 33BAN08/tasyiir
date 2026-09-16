<?php

namespace App\Support\Mock;

class Courses
{
    public static function all(): array
    {
        static $courses = null;
        if ($courses !== null) {
            return $courses;
        }

        $raw = [
            ['name' => 'English A1', 'level' => 'مبتدئ', 'teacher' => 'أستاذ يوسف الفاسي', 'groups' => 2, 'students' => 34, 'price' => 900, 'status' => 'نشط'],
            ['name' => 'English A2', 'level' => 'ابتدائي متقدم', 'teacher' => 'أستاذة سلمى برادة', 'groups' => 2, 'students' => 29, 'price' => 950, 'status' => 'نشط'],
            ['name' => 'English B1', 'level' => 'متوسط', 'teacher' => 'أستاذ يوسف الفاسي', 'groups' => 2, 'students' => 31, 'price' => 1000, 'status' => 'نشط'],
            ['name' => 'English B2', 'level' => 'متوسط متقدم', 'teacher' => 'أستاذة سلمى برادة', 'groups' => 1, 'students' => 14, 'price' => 1100, 'status' => 'نشط'],
            ['name' => 'Français A1', 'level' => 'مبتدئ', 'teacher' => 'أستاذ كريم العلوي', 'groups' => 2, 'students' => 27, 'price' => 900, 'status' => 'نشط'],
            ['name' => 'Français B1', 'level' => 'متوسط', 'teacher' => 'أستاذة نور الإدريسي', 'groups' => 2, 'students' => 24, 'price' => 1000, 'status' => 'نشط'],
            ['name' => 'Mathématiques', 'level' => 'الثانوي', 'teacher' => 'أستاذ عادل بنموسى', 'groups' => 2, 'students' => 30, 'price' => 700, 'status' => 'نشط'],
            ['name' => 'Informatique', 'level' => 'مبتدئ إلى متوسط', 'teacher' => 'أستاذ رشيد الوردي', 'groups' => 2, 'students' => 26, 'price' => 850, 'status' => 'نشط'],
            ['name' => 'IELTS', 'level' => 'متقدم', 'teacher' => 'أستاذ طارق المنصوري', 'groups' => 2, 'students' => 22, 'price' => 1500, 'status' => 'نشط'],
            ['name' => 'TOEFL', 'level' => 'متقدم', 'teacher' => 'أستاذة ليلى الحسني', 'groups' => 1, 'students' => 11, 'price' => 1500, 'status' => 'نشط'],
            ['name' => 'Espagnol A1', 'level' => 'مبتدئ', 'teacher' => 'أستاذ سعيد بوزيان', 'groups' => 1, 'students' => 15, 'price' => 850, 'status' => 'جديد'],
            ['name' => 'المحاسبة والتدبير', 'level' => 'متوسط', 'teacher' => 'أستاذة مريم الغازي', 'groups' => 2, 'students' => 27, 'price' => 1200, 'status' => 'متوقف مؤقتاً'],
        ];

        $courses = [];
        foreach ($raw as $i => $c) {
            $c['id'] = $i + 1;
            $courses[] = $c;
        }

        return $courses;
    }

    public static function find(int $id): ?array
    {
        foreach (static::all() as $c) {
            if ($c['id'] === $id) {
                return $c;
            }
        }
        return null;
    }

    public static function names(): array
    {
        return array_column(static::all(), 'name');
    }
}
