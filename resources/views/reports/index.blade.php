@extends('layouts.app')

@section('title', 'التقارير')

@section('content')
@php
    $sections = [
        ['title' => 'تقارير الطلاب', 'icon' => 'users', 'tone' => 'brand', 'stats' => ['إجمالي الطلاب' => '250', 'تسجيلات جديدة (شهرياً)' => '32', 'معدل الاستمرارية' => '86%']],
        ['title' => 'التسجيلات', 'icon' => 'clipboard-list', 'tone' => 'blue', 'stats' => ['هذا الشهر' => '32', 'إجمالي التسجيلات' => '54', 'متوسط قيمة التسجيل' => '985 MAD']],
        ['title' => 'الإيرادات', 'icon' => 'banknote', 'tone' => 'violet', 'stats' => ['إيرادات شتنبر' => '224,500 MAD', 'نسبة التحصيل' => '81%', 'مبالغ متبقية' => '38,900 MAD']],
        ['title' => 'المصاريف', 'icon' => 'receipt', 'tone' => 'rose', 'stats' => ['مصاريف شتنبر' => '21,269 MAD', 'أكبر فئة' => 'الكراء', 'المعدل اليومي' => '709 MAD']],
        ['title' => 'أجور الأساتذة', 'icon' => 'wallet', 'tone' => 'amber', 'stats' => ['إجمالي الأجور' => '29,540 MAD', 'المدفوع' => '22,180 MAD', 'المتبقي' => '7,360 MAD']],
        ['title' => 'الحضور', 'icon' => 'calendar-check', 'tone' => 'teal', 'stats' => ['معدل الحضور العام' => '88%', 'حالات الغياب' => '24', 'حالات التأخر' => '11']],
    ];
    $toneClasses = [
        'brand' => 'bg-brand-50 text-brand-600', 'blue' => 'bg-blue-50 text-blue-600', 'violet' => 'bg-violet-50 text-violet-600',
        'rose' => 'bg-rose-50 text-rose-600', 'amber' => 'bg-amber-50 text-amber-600', 'teal' => 'bg-teal-50 text-teal-600',
    ];
    $revenue = \App\Support\Mock\Dashboard::revenueMonths();
@endphp

<x-page-header title="التقارير" subtitle="تقارير شاملة حول جميع أنشطة المركز">
    <button type="button" class="btn-secondary" data-toast="جاري تصدير التقرير... (تجريبي)">
        <x-icon name="download" class="w-4 h-4" /> تصدير PDF
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @foreach ($sections as $s)
        <div class="card p-5 flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl {{ $toneClasses[$s['tone']] }}">
                    <x-icon :name="$s['icon']" class="w-5 h-5" />
                </span>
                <h3 class="font-bold text-ink-800">{{ $s['title'] }}</h3>
            </div>
            <div class="space-y-2">
                @foreach ($s['stats'] as $label => $value)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-500">{{ $label }}</span>
                        <span class="ltr-nums font-semibold text-ink-800">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn-secondary justify-center mt-1" data-toast="فتح التقرير التفصيلي (تجريبي)">
                عرض التفاصيل
                <x-icon name="arrow-left" class="w-4 h-4" />
            </button>
        </div>
    @endforeach
</div>

<x-chart-container id="reportsRevenueChart" title="الإيرادات والمصاريف" subtitle="آخر 6 أشهر">
    <span class="text-xs text-ink-400">MAD</span>
</x-chart-container>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('reportsRevenueChart');
    if (!ctx || !window.Chart) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($revenue['labels'], JSON_UNESCAPED_UNICODE) !!},
            datasets: [
                { label: 'الإيرادات', data: {!! json_encode($revenue['revenue']) !!}, backgroundColor: '#10b981', borderRadius: 6 },
                { label: 'المصاريف', data: {!! json_encode($revenue['expenses']) !!}, backgroundColor: '#f43f5e', borderRadius: 6 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', rtl: true, labels: { font: { family: 'Cairo' } } } },
            scales: { y: { beginAtZero: true }, x: { reverse: true } },
        },
    });
});
</script>
@endsection
