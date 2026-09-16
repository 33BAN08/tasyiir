@extends('layouts.app')

@section('title', 'أجور الأساتذة')

@section('content')
@php
    $rows = \App\Support\Mock\Salaries::all();
    $stats = \App\Support\Mock\Salaries::stats();
    $statusTone = ['مدفوع' => 'success', 'مدفوع جزئياً' => 'warning', 'غير مدفوع' => 'danger'];
@endphp

<x-page-header title="أجور الأساتذة" subtitle="حساب وتتبع أجور فريق التدريس">
    <button type="button" class="btn-primary" data-toast="فتح نموذج صرف أجرة (تجريبي)">
        <x-icon name="plus" class="w-4 h-4" /> صرف أجرة
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card icon="banknote" label="إجمالي الأجور" :value="mad($stats['total'])" tone="brand" />
    <x-stat-card icon="wallet" label="المدفوع" :value="mad($stats['paid'])" tone="blue" />
    <x-stat-card icon="receipt" label="المتبقي" :value="mad($stats['remaining'])" tone="rose" />
</div>

<div class="card overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-ink-50 border-b border-ink-100">
                <tr>
                    <th class="table-head-cell">الأستاذ</th>
                    <th class="table-head-cell">الساعات</th>
                    <th class="table-head-cell">السعر / ساعة</th>
                    <th class="table-head-cell">الراتب</th>
                    <th class="table-head-cell">المدفوع</th>
                    <th class="table-head-cell">المتبقي</th>
                    <th class="table-head-cell">الحالة</th>
                    <th class="table-head-cell"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($rows as $r)
                    <tr class="hover:bg-ink-50/70 transition-colors">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$r['teacher']" size="sm" />
                                <div>
                                    <p class="font-semibold text-ink-800">{{ $r['teacher'] }}</p>
                                    <p class="text-xs text-ink-400">{{ $r['specialty'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell ltr-nums">{{ $r['hours'] }} س</td>
                        <td class="table-cell ltr-nums">{{ mad($r['rate']) }}</td>
                        <td class="table-cell ltr-nums font-semibold text-ink-800">{{ mad($r['salary']) }}</td>
                        <td class="table-cell ltr-nums text-emerald-700">{{ mad($r['paid']) }}</td>
                        <td class="table-cell ltr-nums text-red-600">{{ mad($r['remaining']) }}</td>
                        <td class="table-cell"><x-status-badge :label="$r['status']" :tone="$statusTone[$r['status']] ?? 'neutral'" /></td>
                        <td class="table-cell">
                            <button type="button" class="btn-icon" data-toast="فتح تفاصيل أجرة الأستاذ (تجريبي)" aria-label="عرض"><x-icon name="eye" class="w-4 h-4" /></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-ink-100">
        @foreach ($rows as $r)
            <div class="p-4">
                <div class="flex items-center gap-3 mb-2">
                    <x-avatar :name="$r['teacher']" size="sm" />
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-ink-800 truncate">{{ $r['teacher'] }}</p>
                        <p class="text-xs text-ink-400 truncate">{{ $r['specialty'] }}</p>
                    </div>
                    <x-status-badge :label="$r['status']" :tone="$statusTone[$r['status']] ?? 'neutral'" />
                </div>
                <div class="flex items-center justify-between text-xs text-ink-500 ltr-nums">
                    <span>{{ $r['hours'] }} س</span>
                    <span>الراتب: {{ mad($r['salary']) }}</span>
                    <span>المتبقي: {{ mad($r['remaining']) }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
