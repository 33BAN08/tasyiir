<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Support\Analytics;

class DashboardController extends Controller
{
    public function index()
    {
        // Keep unpaid badges current before counting them.
        Enrollment::rolloverDue();

        return view('dashboard.index', [
            'stats' => Analytics::stats(),
            'revenue' => Analytics::revenueByMonth(6),
            'growth' => Analytics::studentGrowthByMonth(6),
            'recentEnrollments' => Analytics::recentEnrollments(5),
            'upcoming' => Analytics::upcomingClassesToday(4),
            'unnotified' => auth()->user()->can('manage-attendance')
                ? Analytics::unnotifiedAbsencesToday()
                : ['count' => 0, 'group_id' => null],
        ]);
    }
}
