@extends('layouts.app')

@section('title', 'الطلاب')

@section('content')
@php
    $students = \App\Support\Mock\Students::all();
    $stats = \App\Support\Mock\Students::stats();
    $shown = array_slice($students, 0, 10);

    $financeTone = ['مؤدي' => 'success', 'جزئي' => 'warning', 'غير مؤدي' => 'danger'];
    $statusTone = ['نشط' => 'success', 'متوقف' => 'neutral'];
@endphp

<x-page-header title="الطلاب" subtitle="إدارة جميع طلاب المركز">
    <button type="button" class="btn-primary" x-data x-on:click="$dispatch('open-add-student-modal')">
        <x-icon name="plus" class="w-4 h-4" />
        إضافة طالب
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="users" label="إجمالي الطلاب" :value="$stats['total']" tone="brand" />
    <x-stat-card icon="circle-check" label="الطلاب النشطون" :value="$stats['active']" tone="blue" />
    <x-stat-card icon="circle-x" label="الطلاب المتوقفون" :value="$stats['paused']" tone="amber" />
    <x-stat-card icon="triangle-alert" label="الطلاب غير المؤدين" :value="$stats['unpaid']" tone="rose" />
</div>

<x-filter-bar>
    <x-search-bar placeholder="البحث باسم الطالب أو رقم الهاتف..." />
    <select class="select sm:w-44">
        <option>كل الدورات</option>
        @foreach (\App\Support\Mock\Courses::names() as $name)
            <option>{{ $name }}</option>
        @endforeach
    </select>
    <select class="select sm:w-40">
        <option>كل الحالات</option>
        <option>نشط</option>
        <option>متوقف</option>
    </select>
    <select class="select sm:w-44">
        <option>الحالة المالية: الكل</option>
        <option>مؤدي</option>
        <option>جزئي</option>
        <option>غير مؤدي</option>
    </select>
    <button type="button" class="btn-secondary shrink-0">
        <x-icon name="sliders-horizontal" class="w-4 h-4" />
        فلاتر متقدمة
    </button>
</x-filter-bar>

<div class="card overflow-hidden">
    <!-- Desktop table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-ink-50 border-b border-ink-100">
                <tr>
                    <th class="table-head-cell">الطالب</th>
                    <th class="table-head-cell">الهاتف</th>
                    <th class="table-head-cell">الدورة</th>
                    <th class="table-head-cell">المجموعة</th>
                    <th class="table-head-cell">تاريخ التسجيل</th>
                    <th class="table-head-cell">حالة التسجيل</th>
                    <th class="table-head-cell">الحالة المالية</th>
                    <th class="table-head-cell"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($shown as $s)
                    <tr class="hover:bg-ink-50/70 transition-colors">
                        <td class="table-cell">
                            <a href="/students/{{ $s['id'] }}" class="flex items-center gap-3 group">
                                <x-avatar :name="$s['name']" size="sm" />
                                <span class="font-semibold text-ink-800 group-hover:text-brand-700">{{ $s['name'] }}</span>
                            </a>
                        </td>
                        <td class="table-cell ltr-nums">{{ $s['phone'] }}</td>
                        <td class="table-cell">{{ $s['course'] }}</td>
                        <td class="table-cell">{{ $s['group'] }}</td>
                        <td class="table-cell ltr-nums">{{ $s['registered_at'] }}</td>
                        <td class="table-cell"><x-status-badge :label="$s['enrollment_status']" :tone="$statusTone[$s['enrollment_status']] ?? 'neutral'" /></td>
                        <td class="table-cell"><x-status-badge :label="$s['financial_status']" :tone="$financeTone[$s['financial_status']] ?? 'neutral'" /></td>
                        <td class="table-cell">
                            <div class="relative flex items-center justify-end gap-1" x-data="{ open: false }">
                                <a href="/students/{{ $s['id'] }}" class="btn-icon" aria-label="عرض"><x-icon name="eye" class="w-4 h-4" /></a>
                                <button type="button" class="btn-icon" data-toast="فتح نموذج تعديل الطالب (تجريبي)" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                                <button type="button" class="btn-icon" x-on:click="open = !open" aria-label="المزيد"><x-icon name="more-vertical" class="w-4 h-4" /></button>
                                <x-dropdown-panel align="end" width="w-48">
                                    <x-menu-item icon="clipboard-list">تسجيل في دورة</x-menu-item>
                                    <x-menu-item icon="wallet">إضافة دفعة</x-menu-item>
                                    <x-menu-item icon="download">تحميل الملف</x-menu-item>
                                    <div class="my-1 border-t border-ink-100"></div>
                                    <x-menu-item icon="trash-2" :danger="true">حذف الطالب</x-menu-item>
                                </x-dropdown-panel>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden divide-y divide-ink-100">
        @foreach ($shown as $s)
            <a href="/students/{{ $s['id'] }}" class="flex items-center gap-3 p-4">
                <x-avatar :name="$s['name']" size="md" />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-ink-800 truncate">{{ $s['name'] }}</p>
                    <p class="text-xs text-ink-400 truncate">{{ $s['course'] }} · {{ $s['group'] }}</p>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <x-status-badge :label="$s['enrollment_status']" :tone="$statusTone[$s['enrollment_status']] ?? 'neutral'" />
                        <x-status-badge :label="$s['financial_status']" :tone="$financeTone[$s['financial_status']] ?? 'neutral'" />
                    </div>
                </div>
                <x-icon name="chevron-left" class="w-4 h-4 text-ink-300 shrink-0" />
            </a>
        @endforeach
    </div>

    <x-pagination :current="1" :last="6" :total="$stats['total']" :from="1" :to="10" />
</div>

<x-modal id="add-student-modal" title="إضافة طالب جديد">
    <form class="space-y-4" x-on:submit.prevent="open = false; Alpine.store('toasts').push('تمت إضافة الطالب بنجاح (تجريبي)')">
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-1.5">الاسم الكامل</label>
            <input type="text" class="input ps-3" placeholder="مثال: يوسف العلوي" required />
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1.5">رقم الهاتف</label>
                <input type="text" class="input ps-3" placeholder="06XX-XX-XX-XX" />
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1.5">المدينة</label>
                <input type="text" class="input ps-3" placeholder="المدينة" />
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-1.5">الدورة</label>
            <select class="select">
                @foreach (\App\Support\Mock\Courses::names() as $name)
                    <option>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" class="btn-secondary" x-on:click="open = false">إلغاء</button>
            <button type="submit" class="btn-primary" x-on:click="open = false">
                <x-icon name="plus" class="w-4 h-4" /> إضافة الطالب
            </button>
        </div>
    </form>
</x-modal>
@endsection
