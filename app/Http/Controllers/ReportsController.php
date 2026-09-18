<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Support\Analytics;

class ReportsController extends Controller
{
    public function index()
    {
        Enrollment::rolloverDue();

        $month = Analytics::monthLabel(now());
        $collection = Analytics::collectionRate();
        $expenses = Analytics::expensesThisMonth()['stats'];
        $salaries = Analytics::salaryTotals();
        $attendance = Analytics::attendanceThisMonth();

        $enrollmentsThisMonth = Enrollment::whereYear('date', now()->year)->whereMonth('date', now()->month)->count();

        $sections = [
            ['title' => 'تقارير الطلاب', 'icon' => 'users', 'tone' => 'brand', 'stats' => [
                'إجمالي الطلاب' => Student::count(),
                'تسجيلات جديدة (شهرياً)' => $enrollmentsThisMonth,
                'معدل الاستمرارية' => Analytics::retentionRate().'%',
            ]],
            ['title' => 'التسجيلات', 'icon' => 'clipboard-list', 'tone' => 'blue', 'stats' => [
                'هذا الشهر' => $enrollmentsThisMonth,
                'إجمالي التسجيلات' => Enrollment::count(),
                'متوسط قيمة التسجيل' => mad(Analytics::averageEnrollmentValue()),
            ]],
            ['title' => 'الإيرادات', 'icon' => 'banknote', 'tone' => 'violet', 'stats' => [
                "إيرادات {$month}" => mad(Analytics::revenueThisMonth()),
                'نسبة التحصيل' => $collection['rate'].'%',
                'مبالغ متبقية' => mad($collection['remaining']),
            ]],
            ['title' => 'المصاريف', 'icon' => 'receipt', 'tone' => 'rose', 'stats' => [
                "مصاريف {$month}" => mad($expenses['total_this_month']),
                'أكبر فئة' => $expenses['biggest_category'],
                'المعدل اليومي' => mad($expenses['avg_daily']),
            ]],
            ['title' => 'أجور الأساتذة', 'icon' => 'wallet', 'tone' => 'amber', 'stats' => [
                'إجمالي الأجور' => mad($salaries['total']),
                'المدفوع' => mad($salaries['paid']),
                'المتبقي' => mad($salaries['remaining']),
            ]],
            ['title' => 'الحضور', 'icon' => 'calendar-check', 'tone' => 'teal', 'stats' => [
                'معدل الحضور العام' => $attendance['rate'].'%',
                'حالات الغياب' => $attendance['absences'],
                'حالات التأخر' => $attendance['lateness'],
            ]],
        ];

        return view('reports.index', [
            'sections' => $sections,
            'revenue' => Analytics::revenueByMonth(6),
        ]);
    }
}
