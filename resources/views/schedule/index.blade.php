@extends('layouts.app')

@section('title', 'الجدول')

@section('content')
@php
    $week = \App\Support\Mock\Schedule::week();
    $palette = ['bg-brand-50 text-brand-700 border-brand-200', 'bg-blue-50 text-blue-700 border-blue-200', 'bg-violet-50 text-violet-700 border-violet-200', 'bg-amber-50 text-amber-700 border-amber-200', 'bg-rose-50 text-rose-700 border-rose-200'];
@endphp

<x-page-header title="الجدول الأسبوعي" subtitle="توقيت الحصص لجميع المجموعات على مدار الأسبوع">
    <button type="button" class="btn-primary" data-toast="فتح نموذج إضافة حصة (تجريبي)">
        <x-icon name="plus" class="w-4 h-4" /> إضافة حصة
    </button>
</x-page-header>

<div class="card p-4 mb-6 flex flex-wrap items-center gap-3">
    <select class="select sm:w-48">
        <option>كل الأساتذة</option>
        @foreach (\App\Support\Mock\Teachers::names() as $name)
            <option>{{ $name }}</option>
        @endforeach
    </select>
    <select class="select sm:w-40">
        <option>كل القاعات</option>
        <option>القاعة 1</option>
        <option>القاعة 2</option>
        <option>القاعة 3</option>
        <option>القاعة 4</option>
        <option>القاعة 5</option>
        <option>قاعة الحاسوب</option>
    </select>
    <div class="flex items-center gap-1.5 text-xs text-ink-400 ms-auto">
        <span class="w-2.5 h-2.5 rounded-full bg-brand-400"></span> نشط
    </div>
</div>

<div class="overflow-x-auto pb-2 -mx-1 px-1">
    <div class="flex gap-4 min-w-max">
        @foreach (\App\Support\Mock\Schedule::DAYS as $day)
            <div class="w-64 shrink-0">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="font-bold text-ink-800">{{ $day }}</h3>
                    <span class="ltr-nums text-xs text-ink-400">{{ count($week[$day] ?? []) }} حصص</span>
                </div>
                <div class="space-y-3">
                    @forelse ($week[$day] ?? [] as $i => $class)
                        @php $tone = $palette[$i % count($palette)]; @endphp
                        <div class="rounded-xl border p-3.5 {{ $tone }}">
                            <p class="ltr-nums text-xs font-bold mb-1.5">{{ $class['time'] }}</p>
                            <p class="text-sm font-bold text-ink-900">{{ $class['course'] }}</p>
                            <p class="text-xs text-ink-500 mt-0.5">{{ $class['group'] }}</p>
                            <div class="flex items-center gap-1.5 mt-2 text-xs text-ink-600">
                                <x-icon name="graduation-cap" class="w-3.5 h-3.5" /> {{ $class['teacher'] }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 text-xs text-ink-600">
                                <x-icon name="map-pin" class="w-3.5 h-3.5" /> {{ $class['room'] }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-ink-200 p-4 text-center">
                            <p class="text-xs text-ink-400">لا توجد حصص</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
