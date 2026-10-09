<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StudentController;
use App\Http\Middleware\SetLocale;
use App\Livewire\Admin\SignupRequests;
use App\Livewire\Attendance\Index as AttendanceIndex;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Livewire\Enrollments\Index as EnrollmentsIndex;
use App\Livewire\Expenses\Index as ExpensesIndex;
use App\Livewire\Groups\Index as GroupsIndex;
use App\Livewire\Notifications\Index as NotificationsIndex;
use App\Livewire\Payments\Index as PaymentsIndex;
use App\Livewire\Public\CenterSetup;
use App\Livewire\Public\CenterSignup;
use App\Livewire\Salaries\Index as SalariesIndex;
use App\Livewire\Schedule\Index as ScheduleIndex;
use App\Livewire\Students\Index as StudentsIndex;
use App\Livewire\Teachers\Index as TeachersIndex;
use App\Support\Mode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Language switcher for pages rendered before login (guest layout). Same logic
// as /settings/language, which stays as the authenticated Settings entry point.
Route::post('/locale', function (Request $request) {
    $request->validate([
        'locale' => 'required|in:'.implode(',', SetLocale::SUPPORTED),
    ]);

    $request->session()->put('locale', $request->locale);

    if ($user = auth()->user()) {
        $user->update(['locale' => $request->locale]);
    }

    return back();
})->name('locale.switch');

if (Mode::isLocal()) {
    // Local edition: the center owner sets themselves up once, with nobody to
    // approve it. The component 404s as soon as a center exists.
    Route::get('/setup', CenterSetup::class)->name('setup');
} else {
    // Hosted edition only: public signup (instant or reviewed, see
    // tasyiir.signup_requires_approval) and the operator's back office.
    Route::get('/register-center', CenterSignup::class)->middleware('guest')->name('register-center');

    Route::middleware(['auth', 'platform-admin'])->prefix('admin')->group(function () {
        Route::redirect('/', '/admin/signups');
        Route::get('/signups', SignupRequests::class)->name('admin.signups');
    });
}

// The tenant app: signed-in users that belong to a center. A platform admin is
// redirected to /admin here and can never render a tenant page.
Route::middleware(['auth', 'tenant-user'])->group(function () {
    // Reachable even without a valid licence, so an expired center can still
    // read its licence status and take its data out.
    Route::get('/settings', function (Request $request) {
        $tabs = ['center', 'account', 'users', 'roles', 'backup', 'license', 'notifications', 'language', 'appearance'];

        return view('settings.index', [
            'openTab' => in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : 'center',
        ]);
    })->name('settings.index');
    Route::post('/settings/language', function (Request $request) {
        $request->validate([
            'locale' => 'required|in:'.implode(',', SetLocale::SUPPORTED),
        ]);

        $request->session()->put('locale', $request->locale);

        if ($user = auth()->user()) {
            $user->update(['locale' => $request->locale]);
        }

        return back();
    })->name('settings.language');

    // Backup / import (owner-level, like the other sensitive Settings panels).
    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('/settings/backup', [BackupController::class, 'download'])->name('settings.backup');
        Route::get('/settings/backup/students-template', [BackupController::class, 'studentTemplate'])->name('settings.backup.template');
        Route::get('/settings/backup/database/{name}', [BackupController::class, 'downloadDatabase'])->name('settings.backup.database');
    });

    // Everything below needs a valid licence (local edition only; see EnsureLicensed).
    Route::middleware('licensed')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications', NotificationsIndex::class)->name('notifications.index');

        // Each module is gated by a permission (see App\Support\Permissions).
        Route::middleware('permission:manage-students')->group(function () {
            Route::get('/students', StudentsIndex::class)->name('students.index');
            Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
            Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        });

        Route::middleware('permission:manage-courses-groups-teachers')->group(function () {
            Route::get('/teachers', TeachersIndex::class)->name('teachers.index');
            Route::get('/courses', CoursesIndex::class)->name('courses.index');
            Route::get('/groups', GroupsIndex::class)->name('groups.index');
        });

        Route::get('/enrollments', EnrollmentsIndex::class)->middleware('permission:manage-enrollments')->name('enrollments.index');
        Route::get('/attendance', AttendanceIndex::class)->middleware('permission:manage-attendance')->name('attendance.index');
        Route::get('/schedule', ScheduleIndex::class)->middleware('permission:manage-schedule')->name('schedule.index');

        Route::middleware('permission:manage-payments')->group(function () {
            Route::get('/payments', PaymentsIndex::class)->name('payments.index');
            Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        });

        Route::get('/expenses', ExpensesIndex::class)->middleware('permission:manage-expenses')->name('expenses.index');
        Route::get('/salaries', SalariesIndex::class)->middleware('permission:manage-salaries')->name('salaries.index');

        Route::middleware('permission:view-reports')->group(function () {
            Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
            Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
        });
    });
});

require __DIR__.'/auth.php';
