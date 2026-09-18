<?php

namespace App\Http\Controllers;

use App\Support\Analytics;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    /** Period options for the header select: label => months back (0 = since January). */
    public const PERIODS = [
        6 => 'آخر 6 أشهر',
        3 => 'آخر 3 أشهر',
        0 => 'السنة الحالية',
    ];

    public function index(Request $request)
    {
        $period = (int) $request->query('months', 6);
        if (! array_key_exists($period, self::PERIODS)) {
            $period = 6;
        }
        $months = $period === 0 ? (int) now()->month : $period;

        $growth = Analytics::studentGrowthByMonth($months);
        $enrollments = Analytics::enrollmentsByMonth($months);
        $revenue = Analytics::revenueByMonth($months);
        $collection = Analytics::collectionRate();

        $since = 'منذ '.$growth['labels'][0];
        $trends = [
            'students' => Analytics::trend($growth['total']),
            'enrollments' => Analytics::trend($enrollments['count']),
            'revenue' => Analytics::trend($revenue['revenue']),
        ];

        return view('statistics.index', [
            'period' => $period,
            'periods' => self::PERIODS,
            'since' => $since,
            'trends' => $trends,
            'growth' => $growth,
            'enrollments' => $enrollments,
            'revenue' => $revenue,
            'collection' => $collection,
            'topCourses' => Analytics::topCourses(6),
            'topTeachers' => Analytics::topTeachers(6),
        ]);
    }
}
