<?php

namespace App\Support\Mock;

class Payments
{
    protected const METHODS = ['نقداً', 'تحويل بنكي', 'بطاقة بنكية', 'شيك'];

    public static function all(): array
    {
        static $rows = null;
        if ($rows !== null) {
            return $rows;
        }

        $enrollments = Enrollments::all();
        $rows = [];
        $id = 1;

        foreach ($enrollments as $i => $en) {
            $net = $en['price'] - $en['discount'];
            $paid = $net - $en['remaining'];
            if ($paid <= 0) {
                continue;
            }

            $day = 1 + ($i % 27);
            $rows[] = [
                'id' => $id++,
                'student' => $en['student'],
                'student_id' => $en['student_id'],
                'amount' => $paid,
                'method' => self::METHODS[$i % count(self::METHODS)],
                'date' => sprintf('2026-09-%02d', $day),
                'status' => $en['remaining'] === 0 ? 'مؤدي بالكامل' : 'دفعة جزئية',
            ];
        }

        return $rows;
    }

    public static function stats(): array
    {
        $rows = static::all();
        $enrollments = Enrollments::all();
        return [
            'total_revenue' => 284500,
            'paid_this_month' => array_sum(array_column($rows, 'amount')),
            'remaining' => array_sum(array_column($enrollments, 'remaining')),
            'unpaid_students' => 18,
        ];
    }
}
