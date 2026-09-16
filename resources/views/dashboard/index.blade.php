@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
@php
    $stats = \App\Support\Mock\Dashboard::stats();
    $revenue = \App\Support\Mock\Dashboard::revenueMonths();
    $growth = \App\Support\Mock\Dashboard::studentGrowth();
    $recentEnrollments = \App\Support\Mock\Dashboard::recentEnrollments();
    $upcoming = \App\Support\Mock\Dashboard::upcomingClasses();
    $statusTone = ['مكتمل' => 'success', 'جزئي' => 'warning', 'غير مؤدي' => 'danger'];
@endphp

<x-page-header title="مرحباً بك مجدداً، محمد 👋" :subtitle="'إليك نظرة سريعة على نشاط مركزك اليوم — ' . ar_date()">
    <a href="/students" class="btn-secondary"><x-icon name="plus" class="w-4 h-4" /> إضافة طالب</a>
    <a href="/enrollments" class="btn-primary"><x-icon name="clipboard-list" class="w-4 h-4" /> تسجيل جديد</a>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @foreach ($stats as $s)
        <x-stat-card :icon="$s['icon']" :label="$s['label']" :value="$s['value']" :tone="$s['tone']" :trend="$s['trend']" />
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2">
        <x-chart-container id="dashboardRevenueChart" title="نظرة عامة على الإيرادات" subtitle="الإيرادات والمصاريف — آخر 6 أشهر">
            <span class="text-xs font-semibold text-ink-400">MAD</span>
        </x-chart-container>
    </div>
    <x-chart-container id="dashboardGrowthChart" title="نمو الطلاب" subtitle="آخر 6 أشهر" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Recent enrollments -->
    <div class="lg:col-span-2 card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
            <h3 class="text-sm font-bold text-ink-800">أحدث التسجيلات</h3>
            <a href="/enrollments" class="text-xs font-semibold text-brand-600 hover:text-brand-700">عرض الكل</a>
        </div>
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">الطالب</th>
                        <th class="table-head-cell">الدورة</th>
                        <th class="table-head-cell">المجموعة</th>
                        <th class="table-head-cell">التاريخ</th>
                        <th class="table-head-cell">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($recentEnrollments as $r)
                        <tr class="hover:bg-ink-50/70 transition-colors">
                            <td class="table-cell">
                                <a href="/students/{{ $r['student_id'] }}" class="flex items-center gap-2.5 group">
                                    <x-avatar :name="$r['student']" size="sm" />
                                    <span class="font-semibold text-ink-800 group-hover:text-brand-700">{{ $r['student'] }}</span>
                                </a>
                            </td>
                            <td class="table-cell">{{ $r['course'] }}</td>
                            <td class="table-cell">{{ $r['group'] }}</td>
                            <td class="table-cell ltr-nums">{{ $r['date'] }}</td>
                            <td class="table-cell"><x-status-badge :label="$r['status']" :tone="$statusTone[$r['status']] ?? 'neutral'" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="sm:hidden divide-y divide-ink-100">
            @foreach ($recentEnrollments as $r)
                <div class="p-4 flex items-center gap-3">
                    <x-avatar :name="$r['student']" size="sm" />
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-ink-800 truncate">{{ $r['student'] }}</p>
                        <p class="text-xs text-ink-400 truncate">{{ $r['course'] }} · {{ $r['group'] }}</p>
                    </div>
                    <x-status-badge :label="$r['status']" :tone="$statusTone[$r['status']] ?? 'neutral'" />
                </div>
            @endforeach
        </div>
    </div>

    <!-- Upcoming classes -->
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
            <h3 class="text-sm font-bold text-ink-800">الحصص القادمة اليوم</h3>
            <a href="/schedule" class="text-xs font-semibold text-brand-600 hover:text-brand-700">الجدول</a>
        </div>
        <div class="divide-y divide-ink-100">
            @forelse ($upcoming as $class)
                <div class="flex items-center gap-3 px-5 py-3.5">
                    <div class="text-center shrink-0 w-14">
                        <p class="ltr-nums text-xs font-bold text-brand-700">{{ explode(' - ', $class['time'])[0] }}</p>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-ink-800 truncate">{{ $class['course'] }}</p>
                        <p class="text-xs text-ink-400 truncate">{{ $class['teacher'] }} · {{ $class['room'] }}</p>
                    </div>
                </div>
            @empty
                <x-empty-state icon="calendar-days" title="لا توجد حصص اليوم" description="تحقق من الجدول الأسبوعي لمعرفة الحصص القادمة." />
            @endforelse
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="card p-5">
    <h3 class="text-sm font-bold text-ink-800 mb-4">إجراءات سريعة</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <x-quick-action-card icon="user-round-plus" label="إضافة طالب" href="/students" tone="brand" toast="فتح نموذج إضافة طالب (تجريبي)" />
        <x-quick-action-card icon="graduation-cap" label="إضافة أستاذ" href="/teachers" tone="blue" toast="فتح نموذج إضافة أستاذ (تجريبي)" />
        <x-quick-action-card icon="book-open" label="إنشاء دورة" href="/courses" tone="violet" toast="فتح نموذج إنشاء دورة (تجريبي)" />
        <x-quick-action-card icon="users-round" label="إنشاء مجموعة" href="/groups" tone="amber" toast="فتح نموذج إنشاء مجموعة (تجريبي)" />
        <x-quick-action-card icon="clipboard-list" label="تسجيل طالب" href="/enrollments" tone="rose" toast="فتح نموذج تسجيل طالب (تجريبي)" />
        <x-quick-action-card icon="calendar-check" label="إضافة حصة" href="/schedule" tone="teal" toast="فتح نموذج إضافة حصة (تجريبي)" />
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Chart) return;
    Chart.defaults.font.family = 'Cairo';

    const months = {!! json_encode($revenue['labels'], JSON_UNESCAPED_UNICODE) !!};

    new Chart(document.getElementById('dashboardRevenueChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                { label: 'الإيرادات', data: {!! json_encode($revenue['revenue']) !!}, backgroundColor: '#10b981', borderRadius: 6, maxBarThickness: 28 },
                { label: 'المصاريف', data: {!! json_encode($revenue['expenses']) !!}, backgroundColor: '#e5e7eb', borderRadius: 6, maxBarThickness: 28 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true }, x: { reverse: true } },
        },
    });

    new Chart(document.getElementById('dashboardGrowthChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($growth['labels'], JSON_UNESCAPED_UNICODE) !!},
            datasets: [{ label: 'إجمالي الطلاب', data: {!! json_encode($growth['total']) !!}, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: 0.35 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { reverse: true } },
        },
    });
});
</script>
@endsection
