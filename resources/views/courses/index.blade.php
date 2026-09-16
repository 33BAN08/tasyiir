@extends('layouts.app')

@section('title', 'الدورات')

@section('content')
@php
    $courses = \App\Support\Mock\Courses::all();
    $statusTone = ['نشط' => 'success', 'جديد' => 'info', 'متوقف مؤقتاً' => 'neutral'];
@endphp

<x-page-header title="الدورات" subtitle="إدارة الدورات التكوينية المقدمة بالمركز">
    <button type="button" class="btn-primary" data-toast="فتح نموذج إنشاء دورة (تجريبي)">
        <x-icon name="plus" class="w-4 h-4" /> إنشاء دورة
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="book-open" label="إجمالي الدورات" value="12" tone="brand" />
    <x-stat-card icon="users-round" label="إجمالي المجموعات" value="24" tone="blue" />
    <x-stat-card icon="users" label="الطلاب المسجلون" value="250" tone="violet" />
    <x-stat-card icon="banknote" label="متوسط سعر الدورة" value="1,033 MAD" tone="amber" />
</div>

<x-filter-bar>
    <x-search-bar placeholder="البحث باسم الدورة..." />
    <select class="select sm:w-40">
        <option>كل المستويات</option>
        <option>مبتدئ</option>
        <option>متوسط</option>
        <option>متقدم</option>
    </select>
    <select class="select sm:w-40">
        <option>كل الحالات</option>
        <option>نشط</option>
        <option>جديد</option>
        <option>متوقف مؤقتاً</option>
    </select>
</x-filter-bar>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($courses as $c)
        <div class="card p-5 flex flex-col gap-4">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-bold text-ink-900">{{ $c['name'] }}</h3>
                    <p class="text-xs text-ink-400 mt-0.5">{{ $c['level'] }}</p>
                </div>
                <x-status-badge :label="$c['status']" :tone="$statusTone[$c['status']] ?? 'neutral'" />
            </div>

            <div class="flex items-center gap-2.5 text-sm text-ink-600">
                <x-avatar :name="$c['teacher']" size="sm" />
                <span>{{ $c['teacher'] }}</span>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center border-t border-ink-100 pt-4">
                <div>
                    <p class="ltr-nums text-base font-bold text-ink-800">{{ $c['groups'] }}</p>
                    <p class="text-[11px] text-ink-400">مجموعات</p>
                </div>
                <div>
                    <p class="ltr-nums text-base font-bold text-ink-800">{{ $c['students'] }}</p>
                    <p class="text-[11px] text-ink-400">طالب</p>
                </div>
                <div>
                    <p class="ltr-nums text-base font-bold text-ink-800">{{ $c['price'] }}</p>
                    <p class="text-[11px] text-ink-400">MAD / شهر</p>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="button" class="btn-secondary flex-1" data-toast="عرض تفاصيل الدورة (تجريبي)"><x-icon name="eye" class="w-4 h-4" /> عرض</button>
                <button type="button" class="btn-icon" data-toast="فتح نموذج تعديل الدورة (تجريبي)" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
            </div>
        </div>
    @endforeach
</div>
@endsection
