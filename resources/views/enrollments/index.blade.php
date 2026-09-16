@extends('layouts.app')

@section('title', 'التسجيلات')

@section('content')
@php
    $rows = array_slice(\App\Support\Mock\Enrollments::all(), 0, 12);
    $stats = \App\Support\Mock\Enrollments::stats();
    $statusTone = ['مكتمل' => 'success', 'جزئي' => 'warning', 'غير مؤدي' => 'danger'];
@endphp

<x-page-header title="التسجيلات" subtitle="جميع تسجيلات الطلاب في الدورات والمجموعات">
    <button type="button" class="btn-primary" data-toast="فتح نموذج تسجيل جديد (تجريبي)">
        <x-icon name="plus" class="w-4 h-4" /> تسجيل جديد
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="clipboard-list" label="إجمالي التسجيلات" :value="$stats['total']" tone="brand" />
    <x-stat-card icon="calendar-days" label="تسجيلات هذا الشهر" :value="$stats['this_month']" tone="blue" :trend="12" />
    <x-stat-card icon="banknote" label="القيمة الإجمالية" :value="mad($stats['total_value'])" tone="violet" />
    <x-stat-card icon="wallet" label="المبالغ المتبقية" :value="mad($stats['remaining'])" tone="rose" />
</div>

<x-filter-bar>
    <x-search-bar placeholder="البحث باسم الطالب..." />
    <select class="select sm:w-44">
        <option>كل الدورات</option>
        @foreach (\App\Support\Mock\Courses::names() as $name)
            <option>{{ $name }}</option>
        @endforeach
    </select>
    <select class="select sm:w-36">
        <option>كل الحالات</option>
        <option>مكتمل</option>
        <option>جزئي</option>
        <option>غير مؤدي</option>
    </select>
</x-filter-bar>

<div class="card overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-ink-50 border-b border-ink-100">
                <tr>
                    <th class="table-head-cell">الطالب</th>
                    <th class="table-head-cell">الدورة</th>
                    <th class="table-head-cell">المجموعة</th>
                    <th class="table-head-cell">تاريخ التسجيل</th>
                    <th class="table-head-cell">السعر</th>
                    <th class="table-head-cell">الخصم</th>
                    <th class="table-head-cell">المتبقي</th>
                    <th class="table-head-cell">الحالة</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($rows as $r)
                    <tr class="hover:bg-ink-50/70 transition-colors">
                        <td class="table-cell">
                            <a href="/students/{{ $r['student_id'] }}" class="flex items-center gap-3 group">
                                <x-avatar :name="$r['student']" size="sm" />
                                <span class="font-semibold text-ink-800 group-hover:text-brand-700">{{ $r['student'] }}</span>
                            </a>
                        </td>
                        <td class="table-cell">{{ $r['course'] }}</td>
                        <td class="table-cell">{{ $r['group'] }}</td>
                        <td class="table-cell ltr-nums">{{ $r['date'] }}</td>
                        <td class="table-cell ltr-nums">{{ mad($r['price']) }}</td>
                        <td class="table-cell ltr-nums">{{ $r['discount'] > 0 ? mad($r['discount']) : '—' }}</td>
                        <td class="table-cell ltr-nums">{{ mad($r['remaining']) }}</td>
                        <td class="table-cell"><x-status-badge :label="$r['status']" :tone="$statusTone[$r['status']] ?? 'neutral'" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-ink-100">
        @foreach ($rows as $r)
            <div class="p-4">
                <div class="flex items-center gap-3 mb-2">
                    <x-avatar :name="$r['student']" size="sm" />
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-ink-800 truncate">{{ $r['student'] }}</p>
                        <p class="text-xs text-ink-400 truncate">{{ $r['course'] }} · {{ $r['group'] }}</p>
                    </div>
                    <x-status-badge :label="$r['status']" :tone="$statusTone[$r['status']] ?? 'neutral'" />
                </div>
                <div class="flex items-center justify-between text-xs text-ink-500 ltr-nums">
                    <span>{{ $r['date'] }}</span>
                    <span>السعر: {{ mad($r['price']) }}</span>
                    <span>المتبقي: {{ mad($r['remaining']) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <x-pagination :current="1" :last="5" :total="54" :from="1" :to="12" />
</div>
@endsection
