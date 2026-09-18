<?php

if (!function_exists('mad')) {
    /** Format a number as Moroccan Dirhams, e.g. 1250 -> "1,250 MAD" */
    function mad(int|float $amount): string
    {
        return number_format($amount, 0, '.', ',') . ' MAD';
    }
}

if (!function_exists('ar_date')) {
    /** A simple Arabic-formatted Gregorian date, e.g. "الثلاثاء، 16 شتنبر 2026" */
    function ar_date(?\DateTimeInterface $date = null): string
    {
        $date = $date ?? new \DateTime('now');

        $days = [
            'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت',
        ];
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'ماي', 6 => 'يونيو',
            7 => 'يوليوز', 8 => 'غشت', 9 => 'شتنبر', 10 => 'أكتوبر', 11 => 'نونبر', 12 => 'دجنبر',
        ];

        $dayName = $days[$date->format('l')];
        $monthName = $months[(int) $date->format('n')];

        return sprintf('%s، %d %s %d', $dayName, (int) $date->format('j'), $monthName, (int) $date->format('Y'));
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $parts = array_filter($parts);
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
        return mb_strtoupper($first . $last);
    }
}

if (!function_exists('is_active_route')) {
    /** True if the current request path starts with (or equals) the given path — for active nav states. */
    function is_active_route(string $path): bool
    {
        $current = '/' . trim(request()->path(), '/');
        $path = '/' . trim($path, '/');

        if ($path === '/dashboard') {
            return $current === '/dashboard' || $current === '/';
        }

        return $current === $path || str_starts_with($current, $path . '/');
    }
}
