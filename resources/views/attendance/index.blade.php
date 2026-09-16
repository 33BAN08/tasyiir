@extends('layouts.app')

@section('title', 'الحضور')

@section('content')
@php
    $groups = \App\Support\Mock\Groups::all();
    $stateTone = ['حاضر' => 'success', 'متأخر' => 'warning', 'غائب' => 'danger'];
    $stateIcon = ['حاضر' => 'check', 'متأخر' => 'clock', 'غائب' => 'x'];
    $initial = array_map(fn ($r) => ['id' => $r['id'], 'name' => $r['name'], 'phone' => $r['phone'], 'state' => $r['state']], $rows);
@endphp

<x-page-header title="الحضور" subtitle="تسجيل ومتابعة حضور الطلاب لكل حصة">
    <button type="button" class="btn-primary" data-toast="تم حفظ الحضور بنجاح (تجريبي)">
        <x-icon name="check-check" class="w-4 h-4" /> حفظ الحضور
    </button>
</x-page-header>

<div class="card p-4 mb-6">
    <form method="get" action="/attendance" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-medium text-ink-500 mb-1.5">التاريخ</label>
            <input type="date" name="date" value="2026-09-16" class="input ps-3" />
        </div>
        <div>
            <label class="block text-xs font-medium text-ink-500 mb-1.5">المجموعة</label>
            <select name="group" class="select" onchange="this.form.submit()">
                @foreach ($groups as $g)
                    <option value="{{ $g['id'] }}" {{ $g['id'] === $group['id'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <div class="flex items-center gap-4 text-sm text-ink-600 w-full">
                <span class="flex items-center gap-1.5"><x-icon name="graduation-cap" class="w-4 h-4 text-ink-400" /> {{ $group['teacher'] }}</span>
                <span class="flex items-center gap-1.5"><x-icon name="book-open" class="w-4 h-4 text-ink-400" /> {{ $group['course'] }}</span>
            </div>
        </div>
    </form>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <x-stat-card icon="users" label="إجمالي الطلاب" :value="$summary['total']" tone="brand" />
    <x-stat-card icon="check" label="حاضر" :value="$summary['present']" tone="blue" />
    <x-stat-card icon="clock" label="متأخر" :value="$summary['late']" tone="amber" />
    <x-stat-card icon="x" label="غائب" :value="$summary['absent']" tone="rose" />
</div>

<div class="card overflow-hidden" x-data="{ rows: {{ json_encode($initial, JSON_UNESCAPED_UNICODE) }} }">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-ink-50 border-b border-ink-100">
                <tr>
                    <th class="table-head-cell">الطالب</th>
                    <th class="table-head-cell">الهاتف</th>
                    <th class="table-head-cell">الحالة</th>
                    <th class="table-head-cell">تعديل سريع</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                <template x-for="(row, index) in rows" :key="row.id">
                    <tr class="hover:bg-ink-50/70 transition-colors">
                        <td class="table-cell font-semibold text-ink-800" x-text="row.name"></td>
                        <td class="table-cell ltr-nums" x-text="row.phone"></td>
                        <td class="table-cell">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                :class="{
                                    'bg-emerald-50 text-emerald-700 ring-emerald-600/20': row.state === 'حاضر',
                                    'bg-amber-50 text-amber-700 ring-amber-600/20': row.state === 'متأخر',
                                    'bg-red-50 text-red-700 ring-red-600/20': row.state === 'غائب',
                                }"
                                x-text="row.state"
                            ></span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-1.5">
                                <button type="button" class="btn-icon" x-on:click="row.state = 'حاضر'" aria-label="حاضر" title="حاضر"><x-icon name="check" class="w-4 h-4 text-emerald-600" /></button>
                                <button type="button" class="btn-icon" x-on:click="row.state = 'متأخر'" aria-label="متأخر" title="متأخر"><x-icon name="clock" class="w-4 h-4 text-amber-600" /></button>
                                <button type="button" class="btn-icon" x-on:click="row.state = 'غائب'" aria-label="غائب" title="غائب"><x-icon name="x" class="w-4 h-4 text-red-600" /></button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-ink-100">
        <template x-for="row in rows" :key="row.id">
            <div class="p-4 flex items-center justify-between gap-3">
                <div>
                    <p class="font-semibold text-ink-800" x-text="row.name"></p>
                    <p class="text-xs text-ink-400 ltr-nums" x-text="row.phone"></p>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" class="btn-icon" x-on:click="row.state = 'حاضر'"><x-icon name="check" class="w-4 h-4 text-emerald-600" /></button>
                    <button type="button" class="btn-icon" x-on:click="row.state = 'متأخر'"><x-icon name="clock" class="w-4 h-4 text-amber-600" /></button>
                    <button type="button" class="btn-icon" x-on:click="row.state = 'غائب'"><x-icon name="x" class="w-4 h-4 text-red-600" /></button>
                </div>
            </div>
        </template>
    </div>

    @if (empty($initial))
        <x-empty-state icon="calendar-check" title="لا يوجد طلاب في هذه المجموعة" />
    @endif
</div>
@endsection
