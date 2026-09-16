<?php

namespace App\Support\Mock;

class Dashboard
{
    public static function stats(): array
    {
        return [
            ['label' => 'إجمالي الطلاب', 'value' => 250, 'icon' => 'users', 'tone' => 'brand', 'trend' => 8],
            ['label' => 'الأساتذة', 'value' => 15, 'icon' => 'graduation-cap', 'tone' => 'blue', 'trend' => null],
            ['label' => 'الدورات', 'value' => 12, 'icon' => 'book-open', 'tone' => 'violet', 'trend' => null],
            ['label' => 'المجموعات', 'value' => 24, 'icon' => 'users-round', 'tone' => 'amber', 'trend' => null],
            ['label' => 'الطلاب غير المؤدين', 'value' => 18, 'icon' => 'triangle-alert', 'tone' => 'rose', 'trend' => -4],
            ['label' => 'التسجيلات هذا الشهر', 'value' => 32, 'icon' => 'clipboard-list', 'tone' => 'brand', 'trend' => 12],
        ];
    }

    public static function revenueMonths(): array
    {
        return [
            'labels' => ['أبريل', 'ماي', 'يونيو', 'يوليوز', 'غشت', 'شتنبر'],
            'revenue' => [186000, 195500, 172300, 168900, 201200, 224500],
            'expenses' => [98000, 101500, 96200, 94800, 108300, 112400],
        ];
    }

    public static function studentGrowth(): array
    {
        return [
            'labels' => ['أبريل', 'ماي', 'يونيو', 'يوليوز', 'غشت', 'شتنبر'],
            'new' => [18, 22, 15, 12, 28, 32],
            'total' => [176, 198, 205, 212, 231, 250],
        ];
    }

    public static function recentEnrollments(int $limit = 5): array
    {
        return array_slice(Enrollments::all(), 0, $limit);
    }

    public static function upcomingClasses(int $limit = 4): array
    {
        return Schedule::upcoming($limit);
    }
}
