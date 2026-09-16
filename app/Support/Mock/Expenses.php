<?php

namespace App\Support\Mock;

class Expenses
{
    public static function categories(): array
    {
        return [
            ['name' => 'الكراء', 'icon' => 'building-2', 'amount' => 12000, 'tone' => 'blue'],
            ['name' => 'الكهرباء', 'icon' => 'zap', 'amount' => 1450, 'tone' => 'amber'],
            ['name' => 'الإنترنت', 'icon' => 'globe', 'amount' => 599, 'tone' => 'violet'],
            ['name' => 'المعدات', 'icon' => 'package', 'amount' => 3200, 'tone' => 'rose'],
            ['name' => 'التسويق', 'icon' => 'megaphone', 'amount' => 2100, 'tone' => 'brand'],
            ['name' => 'المستلزمات', 'icon' => 'notebook-pen', 'amount' => 870, 'tone' => 'blue'],
            ['name' => 'الصيانة', 'icon' => 'wrench', 'amount' => 640, 'tone' => 'amber'],
            ['name' => 'أخرى', 'icon' => 'more-horizontal', 'amount' => 410, 'tone' => 'violet'],
        ];
    }

    public static function all(): array
    {
        static $rows = null;
        if ($rows !== null) {
            return $rows;
        }

        $items = [
            ['category' => 'الكراء', 'label' => 'كراء المحل - شتنبر', 'amount' => 12000, 'date' => '2026-09-01', 'method' => 'تحويل بنكي'],
            ['category' => 'الكهرباء', 'label' => 'فاتورة الكهرباء - غشت', 'amount' => 1450, 'date' => '2026-09-03', 'method' => 'نقداً'],
            ['category' => 'الإنترنت', 'label' => 'اشتراك الأنترنت الشهري', 'amount' => 599, 'date' => '2026-09-03', 'method' => 'بطاقة بنكية'],
            ['category' => 'المعدات', 'label' => 'شراء طاولات وكراسي', 'amount' => 2400, 'date' => '2026-09-05', 'method' => 'نقداً'],
            ['category' => 'المعدات', 'label' => 'جهاز عرض (بروجيكتور)', 'amount' => 800, 'date' => '2026-09-06', 'method' => 'تحويل بنكي'],
            ['category' => 'التسويق', 'label' => 'إعلانات فيسبوك وانستغرام', 'amount' => 1500, 'date' => '2026-09-08', 'method' => 'بطاقة بنكية'],
            ['category' => 'التسويق', 'label' => 'طباعة منشورات ولافتات', 'amount' => 600, 'date' => '2026-09-09', 'method' => 'نقداً'],
            ['category' => 'المستلزمات', 'label' => 'أوراق ومستلزمات مكتبية', 'amount' => 870, 'date' => '2026-09-10', 'method' => 'نقداً'],
            ['category' => 'الصيانة', 'label' => 'صيانة نظام التكييف', 'amount' => 640, 'date' => '2026-09-12', 'method' => 'نقداً'],
            ['category' => 'أخرى', 'label' => 'مصاريف متنوعة', 'amount' => 410, 'date' => '2026-09-14', 'method' => 'نقداً'],
        ];

        $rows = [];
        foreach ($items as $i => $it) {
            $it['id'] = $i + 1;
            $rows[] = $it;
        }

        return $rows;
    }

    public static function stats(): array
    {
        $total = array_sum(array_column(self::categories(), 'amount'));
        return [
            'total_this_month' => $total,
            'categories_count' => count(self::categories()),
            'biggest_category' => 'الكراء',
            'avg_daily' => (int) round($total / 30),
        ];
    }
}
