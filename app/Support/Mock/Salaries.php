<?php

namespace App\Support\Mock;

class Salaries
{
    public static function all(): array
    {
        static $rows = null;
        if ($rows !== null) {
            return $rows;
        }

        $teachers = Teachers::all();
        $rows = [];

        foreach ($teachers as $i => $t) {
            $rate = [90, 100, 110, 120, 130][$i % 5];
            $salary = $t['hours'] * $rate;
            $paidRatio = [1, 1, 0.5, 1, 0][$i % 5];
            $paid = (int) round($salary * $paidRatio);
            $remaining = $salary - $paid;

            $rows[] = [
                'id' => $t['id'],
                'teacher' => $t['name'],
                'specialty' => $t['specialty'],
                'hours' => $t['hours'],
                'rate' => $rate,
                'salary' => $salary,
                'paid' => $paid,
                'remaining' => $remaining,
                'status' => match (true) {
                    $remaining === 0 => 'مدفوع',
                    $paid > 0 => 'مدفوع جزئياً',
                    default => 'غير مدفوع',
                },
            ];
        }

        return $rows;
    }

    public static function stats(): array
    {
        $rows = static::all();
        return [
            'total' => array_sum(array_column($rows, 'salary')),
            'paid' => array_sum(array_column($rows, 'paid')),
            'remaining' => array_sum(array_column($rows, 'remaining')),
        ];
    }
}
