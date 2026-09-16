@extends('layouts.app')

@section('title', 'الإحصائيات')

@section('content')
@php
    $growth = \App\Support\Mock\Dashboard::studentGrowth();
    $revenue = \App\Support\Mock\Dashboard::revenueMonths();
    $courses = \App\Support\Mock\Courses::all();
    $teachers = \App\Support\Mock\Teachers::all();
    usort($courses, fn ($a, $b) => $b['students'] <=> $a['students']);
    $topCourses = array_slice($courses, 0, 6);
    usort($teachers, fn ($a, $b) => $b['hours'] <=> $a['hours']);
    $topTeachers = array_slice($teachers, 0, 6);
@endphp

<x-page-header title="الإحصائيات" subtitle="تحليلات معمقة حول أداء المركز">
    <select class="select sm:w-40">
        <option>آخر 6 أشهر</option>
        <option>آخر 3 أشهر</option>
        <option>السنة الحالية</option>
    </select>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="users" label="نمو عدد الطلاب" value="+42%" tone="brand" :trend="42" trendLabel="منذ أبريل" />
    <x-stat-card icon="clipboard-list" label="اتجاه التسجيلات" value="+78%" tone="blue" :trend="78" trendLabel="منذ أبريل" />
    <x-stat-card icon="banknote" label="اتجاه الإيرادات" value="+20%" tone="violet" :trend="20" trendLabel="منذ أبريل" />
    <x-stat-card icon="percent" label="نسبة تحصيل المدفوعات" value="81%" tone="amber" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <x-chart-container id="studentGrowthChart" title="نمو عدد الطلاب" subtitle="طلاب جدد مقابل الإجمالي" />
    <x-chart-container id="enrollmentTrendChart" title="اتجاه التسجيلات" subtitle="عدد التسجيلات شهرياً" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <x-chart-container id="revenueTrendChart" title="اتجاه الإيرادات" subtitle="آخر 6 أشهر (MAD)" />
    <x-chart-container id="collectionRateChart" title="نسبة تحصيل المدفوعات" subtitle="المؤدى مقابل المتبقي" height="260px" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h3 class="text-sm font-bold text-ink-800 mb-4">شعبية الدورات (بعدد الطلاب)</h3>
        <div class="space-y-3.5">
            @foreach ($topCourses as $c)
                @php $pct = round($c['students'] / $topCourses[0]['students'] * 100); @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-ink-700">{{ $c['name'] }}</span>
                        <span class="ltr-nums text-ink-500">{{ $c['students'] }} طالب</span>
                    </div>
                    <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-bold text-ink-800 mb-4">حمولة الأساتذة (ساعات / أسبوع)</h3>
        <div class="space-y-3.5">
            @foreach ($topTeachers as $t)
                @php $pct = round($t['hours'] / $topTeachers[0]['hours'] * 100); @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-ink-700">{{ $t['name'] }}</span>
                        <span class="ltr-nums text-ink-500">{{ $t['hours'] }} س</span>
                    </div>
                    <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                        <div class="h-full rounded-full bg-violet-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Chart) return;
    Chart.defaults.font.family = 'Cairo';

    const growthLabels = {!! json_encode($growth['labels'], JSON_UNESCAPED_UNICODE) !!};
    const revenueLabels = {!! json_encode($revenue['labels'], JSON_UNESCAPED_UNICODE) !!};

    new Chart(document.getElementById('studentGrowthChart'), {
        type: 'line',
        data: {
            labels: growthLabels,
            datasets: [
                { label: 'طلاب جدد', data: {!! json_encode($growth['new']) !!}, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: 0.35 },
                { label: 'الإجمالي', data: {!! json_encode($growth['total']) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.08)', fill: true, tension: 0.35 },
            ],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { reverse: true } } },
    });

    new Chart(document.getElementById('enrollmentTrendChart'), {
        type: 'bar',
        data: { labels: growthLabels, datasets: [{ label: 'تسجيلات جديدة', data: {!! json_encode($growth['new']) !!}, backgroundColor: '#8b5cf6', borderRadius: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { reverse: true } } },
    });

    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: { labels: revenueLabels, datasets: [{ label: 'الإيرادات', data: {!! json_encode($revenue['revenue']) !!}, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: 0.35 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { reverse: true } } },
    });

    new Chart(document.getElementById('collectionRateChart'), {
        type: 'doughnut',
        data: { labels: ['مؤدى', 'متبقي'], datasets: [{ data: [81, 19], backgroundColor: ['#10b981', '#fde68a'] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '70%' },
    });
});
</script>
@endsection
