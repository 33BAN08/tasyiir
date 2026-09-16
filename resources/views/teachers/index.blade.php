@extends('layouts.app')

@section('title', 'الأساتذة')

@section('content')
@php
    $teachers = \App\Support\Mock\Teachers::all();
    $stats = \App\Support\Mock\Teachers::stats();
    $statusTone = ['نشط' => 'success', 'في إجازة' => 'warning', 'متوقف' => 'neutral'];
@endphp

<x-page-header title="الأساتذة" subtitle="إدارة فريق التدريس بالمركز">
    <button type="button" class="btn-primary" data-toast="فتح نموذج إضافة أستاذ (تجريبي)">
        <x-icon name="plus" class="w-4 h-4" /> إضافة أستاذ
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="graduation-cap" label="إجمالي الأساتذة" :value="$stats['total']" tone="brand" />
    <x-stat-card icon="circle-check" label="الأساتذة النشطون" :value="$stats['active']" tone="blue" />
    <x-stat-card icon="calendar-days" label="في إجازة" :value="$stats['on_leave']" tone="amber" />
    <x-stat-card icon="clock" label="إجمالي ساعات التدريس" :value="$stats['total_hours']" tone="violet" />
</div>

<x-filter-bar>
    <x-search-bar placeholder="البحث باسم الأستاذ أو التخصص..." />
    <select class="select sm:w-48">
        <option>كل التخصصات</option>
        <option>اللغة الإنجليزية</option>
        <option>اللغة الفرنسية</option>
        <option>الرياضيات</option>
        <option>الإعلاميات</option>
        <option>IELTS / TOEFL</option>
    </select>
    <select class="select sm:w-40">
        <option>كل الحالات</option>
        <option>نشط</option>
        <option>في إجازة</option>
        <option>متوقف</option>
    </select>
</x-filter-bar>

<!-- Desktop table -->
<div class="card overflow-hidden hidden md:block">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-ink-50 border-b border-ink-100">
                <tr>
                    <th class="table-head-cell">الأستاذ</th>
                    <th class="table-head-cell">التخصص</th>
                    <th class="table-head-cell">المجموعات</th>
                    <th class="table-head-cell">الطلاب</th>
                    <th class="table-head-cell">ساعات التدريس</th>
                    <th class="table-head-cell">الحالة</th>
                    <th class="table-head-cell"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($teachers as $t)
                    <tr class="hover:bg-ink-50/70 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$t['name']" size="sm" />
                                <div>
                                    <p class="font-semibold text-ink-800">{{ $t['name'] }}</p>
                                    <p class="text-xs text-ink-400">{{ $t['email'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">{{ $t['specialty'] }}</td>
                        <td class="table-cell ltr-nums">{{ $t['groups'] }}</td>
                        <td class="table-cell ltr-nums">{{ $t['students'] }}</td>
                        <td class="table-cell ltr-nums">{{ $t['hours'] }} س/أسبوع</td>
                        <td class="table-cell"><x-status-badge :label="$t['status']" :tone="$statusTone[$t['status']] ?? 'neutral'" /></td>
                        <td class="table-cell">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" class="btn-icon" data-toast="عرض ملف الأستاذ (تجريبي)" aria-label="عرض"><x-icon name="eye" class="w-4 h-4" /></button>
                                <button type="button" class="btn-icon" data-toast="فتح نموذج تعديل الأستاذ (تجريبي)" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:hidden">
    @foreach ($teachers as $t)
        <div class="card p-4 flex items-center gap-3">
            <x-avatar :name="$t['name']" size="md" />
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-ink-800 truncate">{{ $t['name'] }}</p>
                <p class="text-xs text-ink-400 truncate">{{ $t['specialty'] }}</p>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <x-status-badge :label="$t['status']" :tone="$statusTone[$t['status']] ?? 'neutral'" />
                    <span class="text-xs text-ink-400 ltr-nums">{{ $t['groups'] }} مجموعات</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
