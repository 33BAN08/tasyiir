@extends('layouts.app')

@section('title', $student['name'])

@section('content')
@php
    $enrollment = \App\Support\Mock\Enrollments::all()[$student['id'] - 1] ?? null;
    $attendanceHistory = \App\Support\Mock\Attendance::historyForStudent($student['id']);
    $financeTone = ['مؤدي' => 'success', 'جزئي' => 'warning', 'غير مؤدي' => 'danger'];
    $statusTone = ['نشط' => 'success', 'متوقف' => 'neutral'];
    $stateTone = ['حاضر' => 'success', 'متأخر' => 'warning', 'غائب' => 'danger'];
@endphp

<div class="mb-6 flex items-center gap-2 text-sm text-ink-500">
    <a href="/students" class="hover:text-brand-600 font-medium">الطلاب</a>
    <x-icon name="chevron-left" class="w-3.5 h-3.5" />
    <span class="text-ink-700 font-medium">{{ $student['name'] }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile card -->
    <div class="lg:col-span-1">
        <div class="card p-6 flex flex-col items-center text-center">
            <x-avatar :name="$student['name']" size="xl" />
            <h1 class="mt-4 text-lg font-bold text-ink-900">{{ $student['name'] }}</h1>
            <p class="text-sm text-ink-400">{{ $student['course'] }} · {{ $student['group'] }}</p>
            <div class="flex items-center gap-1.5 mt-3">
                <x-status-badge :label="$student['enrollment_status']" :tone="$statusTone[$student['enrollment_status']] ?? 'neutral'" />
                <x-status-badge :label="$student['financial_status']" :tone="$financeTone[$student['financial_status']] ?? 'neutral'" />
            </div>

            <div class="w-full mt-6 pt-6 border-t border-ink-100 space-y-3 text-start">
                <div class="flex items-center gap-3 text-sm">
                    <x-icon name="phone" class="w-4 h-4 text-ink-400" />
                    <span class="ltr-nums text-ink-700">{{ $student['phone'] }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <x-icon name="mail" class="w-4 h-4 text-ink-400" />
                    <span class="text-ink-700 truncate">{{ $student['email'] }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <x-icon name="map-pin" class="w-4 h-4 text-ink-400" />
                    <span class="text-ink-700">{{ $student['city'] }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <x-icon name="calendar-days" class="w-4 h-4 text-ink-400" />
                    <span class="ltr-nums text-ink-700">تاريخ التسجيل: {{ $student['registered_at'] }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <x-icon name="phone" class="w-4 h-4 text-ink-400" />
                    <span class="ltr-nums text-ink-700">ولي الأمر: {{ $student['guardian_phone'] }}</span>
                </div>
            </div>

            <div class="w-full mt-6 grid grid-cols-2 gap-2">
                <button type="button" class="btn-secondary" data-toast="فتح نموذج تعديل الطالب (تجريبي)"><x-icon name="pencil" class="w-4 h-4" /> تعديل</button>
                <button type="button" class="btn-danger" data-toast="تم حذف الطالب (تجريبي)"><x-icon name="trash-2" class="w-4 h-4" /> حذف</button>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="lg:col-span-2" x-data="{ tab: 'info' }">
        <div class="card p-1.5 flex items-center gap-1 mb-5 overflow-x-auto">
            <button type="button" x-on:click="tab = 'info'" :class="tab === 'info' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100'" class="flex-1 whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">المعلومات الشخصية</button>
            <button type="button" x-on:click="tab = 'enrollments'" :class="tab === 'enrollments' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100'" class="flex-1 whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">التسجيلات</button>
            <button type="button" x-on:click="tab = 'attendance'" :class="tab === 'attendance' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100'" class="flex-1 whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">الحضور</button>
            <button type="button" x-on:click="tab = 'payments'" :class="tab === 'payments' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100'" class="flex-1 whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">المدفوعات</button>
            <button type="button" x-on:click="tab = 'activity'" :class="tab === 'activity' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100'" class="flex-1 whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors">النشاط</button>
        </div>

        <!-- المعلومات الشخصية -->
        <div x-show="tab === 'info'" class="card p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div><p class="text-xs text-ink-400 mb-1">الاسم الكامل</p><p class="text-sm font-semibold text-ink-800">{{ $student['name'] }}</p></div>
            <div><p class="text-xs text-ink-400 mb-1">الجنس</p><p class="text-sm font-semibold text-ink-800">{{ $student['gender'] === 'male' ? 'ذكر' : 'أنثى' }}</p></div>
            <div><p class="text-xs text-ink-400 mb-1">رقم الهاتف</p><p class="ltr-nums text-sm font-semibold text-ink-800">{{ $student['phone'] }}</p></div>
            <div><p class="text-xs text-ink-400 mb-1">البريد الإلكتروني</p><p class="text-sm font-semibold text-ink-800">{{ $student['email'] }}</p></div>
            <div><p class="text-xs text-ink-400 mb-1">المدينة</p><p class="text-sm font-semibold text-ink-800">{{ $student['city'] }}</p></div>
            <div><p class="text-xs text-ink-400 mb-1">هاتف ولي الأمر</p><p class="ltr-nums text-sm font-semibold text-ink-800">{{ $student['guardian_phone'] }}</p></div>
            <div><p class="text-xs text-ink-400 mb-1">الدورة الحالية</p><p class="text-sm font-semibold text-ink-800">{{ $student['course'] }}</p></div>
            <div><p class="text-xs text-ink-400 mb-1">المجموعة</p><p class="text-sm font-semibold text-ink-800">{{ $student['group'] }}</p></div>
        </div>

        <!-- التسجيلات -->
        <div x-show="tab === 'enrollments'" class="card overflow-hidden">
            @if ($enrollment)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-ink-50 border-b border-ink-100">
                            <tr>
                                <th class="table-head-cell">الدورة</th>
                                <th class="table-head-cell">المجموعة</th>
                                <th class="table-head-cell">السعر</th>
                                <th class="table-head-cell">الخصم</th>
                                <th class="table-head-cell">المتبقي</th>
                                <th class="table-head-cell">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            <tr>
                                <td class="table-cell">{{ $enrollment['course'] }}</td>
                                <td class="table-cell">{{ $enrollment['group'] }}</td>
                                <td class="table-cell ltr-nums">{{ mad($enrollment['price']) }}</td>
                                <td class="table-cell ltr-nums">{{ mad($enrollment['discount']) }}</td>
                                <td class="table-cell ltr-nums">{{ mad($enrollment['remaining']) }}</td>
                                <td class="table-cell"><x-status-badge :label="$enrollment['status']" tone="info" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state icon="clipboard-list" title="لا توجد تسجيلات" />
            @endif
        </div>

        <!-- الحضور -->
        <div x-show="tab === 'attendance'" class="card overflow-hidden">
            <div class="divide-y divide-ink-100">
                @foreach ($attendanceHistory as $h)
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="ltr-nums text-sm text-ink-600">{{ $h['date'] }}</span>
                        <x-status-badge :label="$h['state']" :tone="$stateTone[$h['state']] ?? 'neutral'" />
                    </div>
                @endforeach
            </div>
        </div>

        <!-- المدفوعات -->
        <div x-show="tab === 'payments'" class="card overflow-hidden">
            @if ($enrollment)
                <div class="p-5 grid grid-cols-3 gap-4 border-b border-ink-100">
                    <div><p class="text-xs text-ink-400 mb-1">السعر الإجمالي</p><p class="ltr-nums font-bold text-ink-800">{{ mad($enrollment['price'] - $enrollment['discount']) }}</p></div>
                    <div><p class="text-xs text-ink-400 mb-1">المؤدى</p><p class="ltr-nums font-bold text-emerald-600">{{ mad(($enrollment['price'] - $enrollment['discount']) - $enrollment['remaining']) }}</p></div>
                    <div><p class="text-xs text-ink-400 mb-1">المتبقي</p><p class="ltr-nums font-bold text-red-600">{{ mad($enrollment['remaining']) }}</p></div>
                </div>
            @endif
            <div class="p-5">
                <button type="button" class="btn-primary w-full justify-center" data-toast="تم تسجيل الدفعة بنجاح (تجريبي)">
                    <x-icon name="wallet" class="w-4 h-4" /> تسجيل دفعة جديدة
                </button>
            </div>
        </div>

        <!-- النشاط -->
        <div x-show="tab === 'activity'" class="card p-5">
            <ol class="relative border-e-2 border-ink-100 me-3 space-y-6">
                <li class="relative pe-6">
                    <span class="absolute -end-[9px] top-0 w-4 h-4 rounded-full bg-brand-500 ring-4 ring-brand-100"></span>
                    <p class="text-sm font-semibold text-ink-800">تم تسجيل الطالب في {{ $student['course'] }}</p>
                    <p class="ltr-nums text-xs text-ink-400 mt-0.5">{{ $student['registered_at'] }}</p>
                </li>
                <li class="relative pe-6">
                    <span class="absolute -end-[9px] top-0 w-4 h-4 rounded-full bg-blue-500 ring-4 ring-blue-100"></span>
                    <p class="text-sm font-semibold text-ink-800">تسجيل حضور الحصة الأولى</p>
                    <p class="text-xs text-ink-400 mt-0.5">{{ $student['group'] }}</p>
                </li>
                <li class="relative pe-6">
                    <span class="absolute -end-[9px] top-0 w-4 h-4 rounded-full bg-amber-500 ring-4 ring-amber-100"></span>
                    <p class="text-sm font-semibold text-ink-800">إضافة الطالب إلى النظام</p>
                    <p class="text-xs text-ink-400 mt-0.5">بواسطة محمد — مدير المركز</p>
                </li>
            </ol>
        </div>
    </div>
</div>
@endsection
