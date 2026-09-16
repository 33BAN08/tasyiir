@extends('layouts.app')

@section('title', 'المصاريف')

@section('content')
@php
    $categories = \App\Support\Mock\Expenses::categories();
    $rows = \App\Support\Mock\Expenses::all();
    $stats = \App\Support\Mock\Expenses::stats();
    $toneClasses = [
        'brand' => 'bg-brand-50 text-brand-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp

<x-page-header title="المصاريف" subtitle="تتبع مصاريف ونفقات تسيير المركز">
    <button type="button" class="btn-primary" x-data x-on:click="$dispatch('open-add-expense-modal')">
        <x-icon name="plus" class="w-4 h-4" /> إضافة مصروف
    </button>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="receipt" label="إجمالي المصاريف هذا الشهر" :value="mad($stats['total_this_month'])" tone="rose" :trend="6" />
    <x-stat-card icon="layers" label="عدد الفئات" :value="$stats['categories_count']" tone="blue" />
    <x-stat-card icon="building-2" label="أكبر فئة" :value="$stats['biggest_category']" tone="amber" />
    <x-stat-card icon="calendar-days" label="المعدل اليومي" :value="mad($stats['avg_daily'])" tone="violet" />
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach ($categories as $c)
        <div class="card p-4 flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl shrink-0 {{ $toneClasses[$c['tone']] ?? $toneClasses['brand'] }}">
                <x-icon :name="$c['icon']" class="w-5 h-5" />
            </span>
            <div class="min-w-0">
                <p class="text-xs text-ink-400 truncate">{{ $c['name'] }}</p>
                <p class="ltr-nums text-sm font-bold text-ink-800">{{ mad($c['amount']) }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-ink-100">
        <h3 class="text-sm font-bold text-ink-800">المصاريف الأخيرة</h3>
    </div>
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-ink-50 border-b border-ink-100">
                <tr>
                    <th class="table-head-cell">البيان</th>
                    <th class="table-head-cell">الفئة</th>
                    <th class="table-head-cell">المبلغ</th>
                    <th class="table-head-cell">طريقة الدفع</th>
                    <th class="table-head-cell">التاريخ</th>
                    <th class="table-head-cell"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($rows as $r)
                    <tr class="hover:bg-ink-50/70 transition-colors">
                        <td class="table-cell font-semibold text-ink-800">{{ $r['label'] }}</td>
                        <td class="table-cell"><x-status-badge :label="$r['category']" tone="neutral" :dot="false" /></td>
                        <td class="table-cell ltr-nums text-red-600 font-semibold">- {{ mad($r['amount']) }}</td>
                        <td class="table-cell">{{ $r['method'] }}</td>
                        <td class="table-cell ltr-nums">{{ $r['date'] }}</td>
                        <td class="table-cell">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" class="btn-icon" data-toast="فتح نموذج تعديل المصروف (تجريبي)" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                                <button type="button" class="btn-icon" data-toast="تم حذف المصروف (تجريبي)" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-ink-100">
        @foreach ($rows as $r)
            <div class="p-4">
                <div class="flex items-center justify-between mb-1">
                    <p class="font-semibold text-ink-800">{{ $r['label'] }}</p>
                    <p class="ltr-nums text-red-600 font-semibold">- {{ mad($r['amount']) }}</p>
                </div>
                <div class="flex items-center justify-between text-xs text-ink-400">
                    <span>{{ $r['category'] }} · {{ $r['method'] }}</span>
                    <span class="ltr-nums">{{ $r['date'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<x-modal id="add-expense-modal" title="إضافة مصروف جديد">
    <form class="space-y-4" x-on:submit.prevent="open = false; Alpine.store('toasts').push('تمت إضافة المصروف بنجاح (تجريبي)')">
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-1.5">البيان</label>
            <input type="text" class="input ps-3" placeholder="مثال: فاتورة الكهرباء" required />
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1.5">الفئة</label>
                <select class="select">
                    @foreach ($categories as $c)
                        <option>{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-ink-700 mb-1.5">المبلغ (MAD)</label>
                <input type="number" class="input ps-3" placeholder="0.00" />
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" class="btn-secondary" x-on:click="open = false">إلغاء</button>
            <button type="submit" class="btn-primary"><x-icon name="plus" class="w-4 h-4" /> إضافة</button>
        </div>
    </form>
</x-modal>
@endsection
