<?php

namespace App\Support\Mock;

/**
 * Centralized mock data — Teachers.
 *
 * Phase 1 note: everything below is static, in-memory sample data used only
 * to build the UI. In Phase 2 this class's public methods should be the only
 * thing that changes — swap the arrays for real Eloquent queries
 * (e.g. Teacher::with('groups')->paginate()) and every view that calls
 * Teachers::all() / Teachers::find() keeps working unmodified.
 */
class Teachers
{
    public static function all(): array
    {
        static $teachers = null;

        if ($teachers !== null) {
            return $teachers;
        }

        $raw = [
            ['name' => 'أستاذ يوسف الفاسي', 'specialty' => 'اللغة الإنجليزية', 'phone' => '0661-22-33-44', 'email' => 'y.fassi@planzeen.ma', 'groups' => 3, 'students' => 54, 'hours' => 48, 'status' => 'نشط'],
            ['name' => 'أستاذة سلمى برادة', 'specialty' => 'اللغة الإنجليزية', 'phone' => '0662-11-22-33', 'email' => 's.berrada@planzeen.ma', 'groups' => 2, 'students' => 38, 'hours' => 32, 'status' => 'نشط'],
            ['name' => 'أستاذ كريم العلوي', 'specialty' => 'اللغة الفرنسية', 'phone' => '0663-44-55-66', 'email' => 'k.alaoui@planzeen.ma', 'groups' => 3, 'students' => 47, 'hours' => 40, 'status' => 'نشط'],
            ['name' => 'أستاذة نور الإدريسي', 'specialty' => 'اللغة الفرنسية', 'phone' => '0664-55-66-77', 'email' => 'n.idrissi@planzeen.ma', 'groups' => 2, 'students' => 33, 'hours' => 28, 'status' => 'نشط'],
            ['name' => 'أستاذ عادل بنموسى', 'specialty' => 'الرياضيات', 'phone' => '0665-66-77-88', 'email' => 'a.benmoussa@planzeen.ma', 'groups' => 2, 'students' => 29, 'hours' => 24, 'status' => 'نشط'],
            ['name' => 'أستاذة إيمان الشرقاوي', 'specialty' => 'الرياضيات', 'phone' => '0666-77-88-99', 'email' => 'i.charkaoui@planzeen.ma', 'groups' => 1, 'students' => 16, 'hours' => 12, 'status' => 'نشط'],
            ['name' => 'أستاذ رشيد الوردي', 'specialty' => 'الإعلاميات', 'phone' => '0667-88-99-00', 'email' => 'r.ouardi@planzeen.ma', 'groups' => 2, 'students' => 31, 'hours' => 26, 'status' => 'نشط'],
            ['name' => 'أستاذة حنان زروالي', 'specialty' => 'الإعلاميات', 'phone' => '0668-99-00-11', 'email' => 'h.zerouali@planzeen.ma', 'groups' => 1, 'students' => 14, 'hours' => 10, 'status' => 'في إجازة'],
            ['name' => 'أستاذ طارق المنصوري', 'specialty' => 'IELTS / TOEFL', 'phone' => '0669-00-11-22', 'email' => 't.mansouri@planzeen.ma', 'groups' => 2, 'students' => 22, 'hours' => 20, 'status' => 'نشط'],
            ['name' => 'أستاذة ليلى الحسني', 'specialty' => 'IELTS / TOEFL', 'phone' => '0660-11-22-33', 'email' => 'l.hassani@planzeen.ma', 'groups' => 1, 'students' => 11, 'hours' => 10, 'status' => 'نشط'],
            ['name' => 'أستاذ سعيد بوزيان', 'specialty' => 'اللغة الإسبانية', 'phone' => '0671-22-33-44', 'email' => 's.bouzian@planzeen.ma', 'groups' => 1, 'students' => 15, 'hours' => 8, 'status' => 'نشط'],
            ['name' => 'أستاذة مريم الغازي', 'specialty' => 'المحاسبة', 'phone' => '0672-33-44-55', 'email' => 'm.ghazi@planzeen.ma', 'groups' => 2, 'students' => 27, 'hours' => 18, 'status' => 'نشط'],
            ['name' => 'أستاذ حمزة الجابري', 'specialty' => 'اللغة الإنجليزية', 'phone' => '0673-44-55-66', 'email' => 'h.jabri@planzeen.ma', 'groups' => 1, 'students' => 12, 'hours' => 8, 'status' => 'متوقف'],
            ['name' => 'أستاذة أسماء الوهابي', 'specialty' => 'اللغة الفرنسية', 'phone' => '0674-55-66-77', 'email' => 'a.wahabi@planzeen.ma', 'groups' => 1, 'students' => 18, 'hours' => 10, 'status' => 'نشط'],
            ['name' => 'أستاذ يونس الصبار', 'specialty' => 'الرياضيات', 'phone' => '0675-66-77-88', 'email' => 'y.sabbar@planzeen.ma', 'groups' => 1, 'students' => 13, 'hours' => 8, 'status' => 'نشط'],
        ];

        $teachers = [];
        foreach ($raw as $i => $t) {
            $t['id'] = $i + 1;
            $t['initials'] = mb_substr($t['name'], -1);
            $teachers[] = $t;
        }

        return $teachers;
    }

    public static function find(int $id): ?array
    {
        foreach (static::all() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }
        return null;
    }

    public static function names(): array
    {
        return array_column(static::all(), 'name');
    }

    public static function stats(): array
    {
        $all = static::all();
        return [
            'total' => 15, // headline figure per product spec — sample list above is a representative subset
            'active' => count(array_filter($all, fn ($t) => $t['status'] === 'نشط')),
            'on_leave' => count(array_filter($all, fn ($t) => $t['status'] === 'في إجازة')),
            'total_hours' => array_sum(array_column($all, 'hours')),
        ];
    }
}
