<?php

use App\Routing\Route;
use App\Support\Mock;

Route::get('/', fn () => view('dashboard.index'))->name('dashboard');
Route::get('/dashboard', fn () => view('dashboard.index'))->name('dashboard');

Route::get('/students', fn () => view('students.index'))->name('students.index');
Route::get('/students/{id}', function ($id) {
    $student = Mock\Students::find((int) $id) ?? Mock\Students::all()[0];
    return view('students.show', ['student' => $student]);
})->name('students.show');

Route::get('/teachers', fn () => view('teachers.index'))->name('teachers.index');
Route::get('/courses', fn () => view('courses.index'))->name('courses.index');
Route::get('/groups', fn () => view('groups.index'))->name('groups.index');
Route::get('/enrollments', fn () => view('enrollments.index'))->name('enrollments.index');
Route::get('/attendance', function () {
    $groupId = (int) ($_GET['group'] ?? Mock\Groups::all()[0]['id']);
    $group = Mock\Groups::find($groupId) ?? Mock\Groups::all()[0];
    $rows = Mock\Attendance::forGroup($group['id']);
    return view('attendance.index', [
        'group' => $group,
        'rows' => $rows,
        'summary' => Mock\Attendance::summary($rows),
    ]);
})->name('attendance.index');
Route::get('/schedule', fn () => view('schedule.index'))->name('schedule.index');
Route::get('/payments', fn () => view('payments.index'))->name('payments.index');
Route::get('/expenses', fn () => view('expenses.index'))->name('expenses.index');
Route::get('/salaries', fn () => view('salaries.index'))->name('salaries.index');
Route::get('/reports', fn () => view('reports.index'))->name('reports.index');
Route::get('/statistics', fn () => view('statistics.index'))->name('statistics.index');
Route::get('/notifications', fn () => view('notifications.index'))->name('notifications.index');
Route::get('/settings', fn () => view('settings.index'))->name('settings.index');
