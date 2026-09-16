<?php

namespace App\Support\Mock;

class Enrollments
{
    public static function all(): array
    {
        static $rows = null;
        if ($rows !== null) {
            return $rows;
        }

        $students = Students::all();
        $courses = Courses::all();
        $rows = [];

        foreach ($students as $i => $s) {
            $course = null;
            foreach ($courses as $c) {
                if ($c['name'] === $s['course']) {
                    $course = $c;
                    break;
                }
            }
            $price = $course['price'] ?? 900;
            $discount = [0, 0, 50, 0, 100, 0, 0, 150][$i % 8];
            $net = $price - $discount;

            $paidRatio = match ($s['financial_status']) {
                'مؤدي' => 1.0,
                'جزئي' => 0.5,
                default => 0.0,
            };
            $paid = (int) round($net * $paidRatio);
            $remaining = $net - $paid;

            $rows[] = [
                'id' => $s['id'],
                'student' => $s['name'],
                'student_id' => $s['id'],
                'course' => $s['course'],
                'group' => $s['group'],
                'date' => $s['registered_at'],
                'price' => $price,
                'discount' => $discount,
                'remaining' => $remaining,
                'status' => match (true) {
                    $remaining === 0 => 'مكتمل',
                    $paid > 0 => 'جزئي',
                    default => 'غير مؤدي',
                },
            ];
        }

        return $rows;
    }

    public static function stats(): array
    {
        $all = static::all();
        return [
            'total' => count($all),
            'this_month' => 32, // headline figure per product spec
            'total_value' => array_sum(array_map(fn ($r) => $r['price'] - $r['discount'], $all)),
            'remaining' => array_sum(array_column($all, 'remaining')),
        ];
    }
}
