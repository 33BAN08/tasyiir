@extends('layouts.app')

@section('title', 'الإحصائيات')

@section('content')

<x-page-header title="الإحصائيات" subtitle="تحليلات معمقة حول أداء المركز">
    <form method="GET" action="{{ route('statistics.index') }}">
        <select name="months" class="select sm:w-40" onchange="this.form.submit()" aria-label="الفترة">
            @foreach ($periods as $value => $label)
                <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php $fmt = fn ($t) => $t === null ? '—' : (($t >= 0 ? '+' : '') . $t . '%'); @endphp
    <x-stat-card icon="users" label="نمو عدد الطلاب" :value="$fmt($trends['students'])" tone="brand" :trend="$trends['students']" :trendLabel="$since" />
    <x-stat-card icon="clipboard-list" label="اتجاه التسجيلات" :value="$fmt($trends['enrollments'])" tone="blue" :trend="$trends['enrollments']" :trendLabel="$since" />
    <x-stat-card icon="banknote" label="اتجاه الإيرادات" :value="$fmt($trends['revenue'])" tone="violet" :trend="$trends['revenue']" :trendLabel="$since" />
    <x-stat-card icon="percent" label="نسبة تحصيل المدفوعات" :value="$collection['rate'] . '%'" tone="amber" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <x-chart-container id="studentGrowthChart" title="نمو عدد الطلاب" subtitle="طلاب جدد مقابل الإجمالي" />
    <x-chart-container id="enrollmentTrendChart" title="اتجاه التسجيلات" subtitle="عدد التسجيلات شهرياً" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <x-chart-container id="revenueTrendChart" title="اتجاه الإيرادات" subtitle="{{ $periods[$period] }} (MAD)" />
    <x-chart-container id="collectionRateChart" title="نسبة تحصيل المدفوعات" subtitle="المؤدى مقابل المتبقي" height="260px" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h3 class="text-sm font-bold text-ink-800 mb-4">شعبية الدورات (بعدد الطلاب)</h3>
        <div class="space-y-3.5">
            @forelse ($topCourses as $c)
                @php $pct = $topCourses[0]['students'] > 0 ? round($c['students'] / $topCourses[0]['students'] * 100) : 0; @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-ink-700">{{ $c['name'] }}</span>
                        <span class="ltr-nums text-ink-500">{{ $c['students'] }} طالب</span>
                    </div>
                    <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-400">لا توجد دورات بعد.</p>
            @endforelse
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-bold text-ink-800 mb-4">حمولة الأساتذة (ساعات / أسبوع)</h3>
        <div class="space-y-3.5">
            @forelse ($topTeachers as $t)
                @php $pct = $topTeachers[0]['hours'] > 0 ? round($t['hours'] / $topTeachers[0]['hours'] * 100) : 0; @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-ink-700">{{ $t['name'] }}</span>
                        <span class="ltr-nums text-ink-500">{{ $t['hours'] }} س</span>
                    </div>
                    <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                        <div class="h-full rounded-full bg-violet-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-400">لا يوجد أساتذة بعد.</p>
            @endforelse
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
        data: { labels: growthLabels, datasets: [{ label: 'تسجيلات جديدة', data: {!! json_encode($enrollments['count']) !!}, backgroundColor: '#8b5cf6', borderRadius: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { reverse: true } } },
    });

    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: { labels: revenueLabels, datasets: [{ label: 'الإيرادات', data: {!! json_encode($revenue['revenue']) !!}, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.12)', fill: true, tension: 0.35 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { reverse: true } } },
    });

    new Chart(document.getElementById('collectionRateChart'), {
        type: 'doughnut',
        data: { labels: ['مؤدى', 'متبقي'], datasets: [{ data: {!! json_encode([$collection['paid'], $collection['remaining']]) !!}, backgroundColor: ['#10b981', '#fde68a'] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '70%' },
    });
});
</script>
@endsection
