@extends('layouts.app')

@section('title', 'المجموعات')

@section('content')
@php
    $groups = \App\Support\Mock\Groups::all();
    $statusTone = ['نشط' => 'success', 'جديد' => 'info', 'متوقف مؤقتاً' => 'neutral'];
@endphp

<x-page-header title="المجموعات" subtitle="إدارة مجموعات وأفواج الدراسة">
    <button type="button" class="btn-primary" data-toast="فتح نموذج إنشاء مجموعة (تجريبي)">
        <x-icon name="plus" class="w-4 h-4" /> إنشاء مجموعة
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="users-round" label="إجمالي المجموعات" value="24" tone="brand" />
    <x-stat-card icon="circle-check" label="مجموعات نشطة" :value="count(array_filter($groups, fn($g) => $g['status'] === 'نشط'))" tone="blue" />
    <x-stat-card icon="users" label="متوسط الطلاب / مجموعة" value="14" tone="violet" />
    <x-stat-card icon="building-2" label="القاعات المستعملة" value="6" tone="amber" />
</div>

<x-filter-bar>
    <x-search-bar placeholder="البحث باسم المجموعة أو الدورة..." />
    <select class="select sm:w-44">
        <option>كل الدورات</option>
        @foreach (\App\Support\Mock\Courses::names() as $name)
            <option>{{ $name }}</option>
        @endforeach
    </select>
    <select class="select sm:w-40">
        <option>كل الحالات</option>
        <option>نشط</option>
        <option>جديد</option>
        <option>متوقف مؤقتاً</option>
    </select>
</x-filter-bar>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($groups as $g)
        @php $fill = $g['capacity'] > 0 ? min(100, round($g['students'] / $g['capacity'] * 100)) : 0; @endphp
        <div class="card p-5 flex flex-col gap-3.5">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-bold text-ink-900">{{ $g['name'] }}</h3>
                    <p class="text-xs text-ink-400 mt-0.5">{{ $g['course'] }}</p>
                </div>
                <x-status-badge :label="$g['status']" :tone="$statusTone[$g['status']] ?? 'neutral'" />
            </div>

            <div class="flex items-center gap-2.5 text-sm text-ink-600">
                <x-avatar :name="$g['teacher']" size="sm" />
                <span class="truncate">{{ $g['teacher'] }}</span>
            </div>

            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-ink-400">عدد الطلاب</span>
                    <span class="ltr-nums font-semibold text-ink-700">{{ $g['students'] }} / {{ $g['capacity'] }}</span>
                </div>
                <div class="h-1.5 rounded-full bg-ink-100 overflow-hidden">
                    <div class="h-full rounded-full bg-brand-500" style="width: {{ $fill }}%"></div>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-ink-500">
                <x-icon name="map-pin" class="w-3.5 h-3.5 text-ink-400" /> {{ $g['room'] }}
            </div>
            <div class="flex items-center gap-2 text-xs text-ink-500">
                <x-icon name="clock" class="w-3.5 h-3.5 text-ink-400" /> {{ $g['schedule'] }}
            </div>

            <div class="flex items-center gap-2 pt-1 border-t border-ink-100 mt-1">
                <button type="button" class="btn-secondary flex-1" data-toast="عرض تفاصيل المجموعة (تجريبي)"><x-icon name="eye" class="w-4 h-4" /> عرض</button>
                <a href="/attendance?group={{ $g['id'] }}" class="btn-icon" aria-label="الحضور"><x-icon name="calendar-check" class="w-4 h-4" /></a>
            </div>
        </div>
    @endforeach
</div>
@endsection
