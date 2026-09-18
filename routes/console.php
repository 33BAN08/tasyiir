<?php

use App\Models\Enrollment;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fully paid enrollments whose due date has passed become unpaid for the new
// month. Also runs lazily when the Enrollments/Students pages load, so this is
// a safety net for tenants nobody has opened recently.
Artisan::command('enrollments:rollover', function () {
    $count = Enrollment::rolloverDue();
    $this->info("Rolled over {$count} enrollment(s) past their due date.");
})->purpose('Mark fully paid enrollments past their due date as unpaid again');

Schedule::command('enrollments:rollover')->dailyAt('00:05');
