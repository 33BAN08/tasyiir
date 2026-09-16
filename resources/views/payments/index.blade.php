@extends('layouts.app')

@section('title', 'أداءات الطلاب')

@section('content')
@php
    $rows = array_slice(\App\Support\Mock\Payments::all(), 0, 12);
    $stats = \App\Support\Mock\Payments::stats();
    $statusTone = ['مؤدي بالكامل' => 'success', 'دفعة جزئية' => 'warning'];
@endphp

<x-page-header title="أداءات الطلاب" subtitle="متابعة مدفوعات ورسوم الطلاب">
    <button type="button" class="btn-primary" data-toast="فتح نموذج تسجيل دفعة (تجريبي)">
        <x-icon name="plus" class="w-4 h-4" /> تسجيل دفعة
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="banknote" label="إجمالي المدخول" :value="mad($stats['total_revenue'])" tone="brand" :trend="9" />
    <x-stat-card icon="wallet" label="المدفوع هذا الشهر" :value="mad($stats['paid_this_month'])" tone="blue" />
    <x-stat-card icon="receipt" label="المبالغ المتبقية" :value="mad($stats['remaining'])" tone="amber" />
    <x-stat-card icon="triangle-alert" label="الطلاب غير المؤدين" :value="$stats['unpaid_students']" tone="rose" />
</div>

<x-filter-bar>
    <x-search-bar placeholder="البحث باسم الطالب..." />
    <select class="select sm:w-40">
        <option>كل طرق الدفع</option>
        <option>نقداً</option>
        <option>تحويل بنكي</option>
        <option>بطاقة بنكية</option>
        <option>شيك</option>
    </select>
    <select class="select sm:w-36">
        <option>كل الحالات</option>
        <option>مؤدي بالكامل</option>
        <option>دفعة جزئية</option>
    </select>
</x-filter-bar>

<div class="card overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-ink-50 border-b border-ink-100">
                <tr>
                    <th class="table-head-cell">الطالب</th>
                    <th class="table-head-cell">المبلغ</th>
                    <th class="table-head-cell">طريقة الدفع</th>
                    <th class="table-head-cell">التاريخ</th>
                    <th class="table-head-cell">الحالة</th>
                    <th class="table-head-cell"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($rows as $p)
                    <tr class="hover:bg-ink-50/70 transition-colors">
                        <td class="table-cell">
                            <a href="/students/{{ $p['student_id'] }}" class="flex items-center gap-3 group">
                                <x-avatar :name="$p['student']" size="sm" />
                                <span class="font-semibold text-ink-800 group-hover:text-brand-700">{{ $p['student'] }}</span>
                            </a>
                        </td>
                        <td class="table-cell ltr-nums font-semibold text-emerald-700">{{ mad($p['amount']) }}</td>
                        <td class="table-cell">{{ $p['method'] }}</td>
                        <td class="table-cell ltr-nums">{{ $p['date'] }}</td>
                        <td class="table-cell"><x-status-badge :label="$p['status']" :tone="$statusTone[$p['status']] ?? 'neutral'" /></td>
                        <td class="table-cell">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" class="btn-icon" data-toast="تحميل إيصال الدفع (تجريبي)" aria-label="إيصال"><x-icon name="download" class="w-4 h-4" /></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-ink-100">
        @foreach ($rows as $p)
            <div class="p-4 flex items-center gap-3">
                <x-avatar :name="$p['student']" size="sm" />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-ink-800 truncate">{{ $p['student'] }}</p>
                    <p class="text-xs text-ink-400 ltr-nums">{{ $p['date'] }} · {{ $p['method'] }}</p>
                </div>
                <div class="text-end">
                    <p class="ltr-nums font-bold text-emerald-700">{{ mad($p['amount']) }}</p>
                    <x-status-badge :label="$p['status']" :tone="$statusTone[$p['status']] ?? 'neutral'" />
                </div>
            </div>
        @endforeach
    </div>

    <x-pagination :current="1" :last="4" :total="42" :from="1" :to="12" />
</div>
@endsection
