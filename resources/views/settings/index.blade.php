@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')

<x-page-header title="الإعدادات" subtitle="إدارة إعدادات المركز والحساب والنظام" />

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6" x-data="{ tab: 'center' }">
    <!-- Side nav -->
    <div class="lg:col-span-1">
        <div class="card p-2 flex lg:flex-col gap-1 overflow-x-auto">
            @foreach ([
                ['key' => 'center', 'label' => 'معلومات المركز', 'icon' => 'building-2'],
                ['key' => 'account', 'label' => 'الحساب', 'icon' => 'user-round-plus'],
                ['key' => 'users', 'label' => 'المستخدمون', 'icon' => 'users', 'can' => 'manage-users'],
                ['key' => 'roles', 'label' => 'الصلاحيات', 'icon' => 'shield-check', 'can' => 'manage-users'],
                ['key' => 'notifications', 'label' => 'الإشعارات', 'icon' => 'bell'],
                ['key' => 'language', 'label' => 'اللغة', 'icon' => 'languages'],
                ['key' => 'appearance', 'label' => 'المظهر', 'icon' => 'palette'],
            ] as $item)
                @continue (isset($item['can']) && ! auth()->user()->can($item['can']))
                <button
                    type="button"
                    x-on:click="tab = '{{ $item['key'] }}'"
                    :class="tab === '{{ $item['key'] }}' ? 'bg-brand-50 text-brand-700' : 'text-ink-600 hover:bg-ink-100'"
                    class="flex items-center gap-2.5 whitespace-nowrap px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors w-full"
                >
                    <x-icon :name="$item['icon']" class="w-4 h-4" />
                    {{ $item['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Panels -->
    <div class="lg:col-span-3 space-y-6">
        <!-- معلومات المركز -->
        <div x-show="tab === 'center'" class="card p-6">
            <livewire:settings.center-profile />
        </div>

        <!-- الحساب -->
        <div x-show="tab === 'account'" class="card p-6">
            <livewire:settings.account />
        </div>

        @can('manage-users')
            <!-- المستخدمون -->
            <div x-show="tab === 'users'" class="card overflow-hidden">
                <livewire:settings.team />
            </div>

            <!-- الصلاحيات -->
            <div x-show="tab === 'roles'" class="card overflow-hidden">
                <livewire:settings.roles />
            </div>
        @endcan

        <!-- الإشعارات -->
        <div x-show="tab === 'notifications'" class="card p-6">
            <h3 class="font-bold text-ink-800 mb-5">تفضيلات الإشعارات</h3>
            <div class="divide-y divide-ink-100">
                @foreach ([
                    ['label' => 'تسجيل طالب جديد', 'desc' => 'إشعار عند إضافة طالب جديد إلى النظام'],
                    ['label' => 'استلام دفعة', 'desc' => 'إشعار عند تسجيل دفعة جديدة من طالب'],
                    ['label' => 'الطلاب غير المؤدين', 'desc' => 'تذكير أسبوعي بالطلاب المتأخرين عن الدفع'],
                    ['label' => 'الحصص القادمة', 'desc' => 'تنبيه قبل بداية كل حصة بـ 30 دقيقة'],
                ] as $i => $pref)
                    <div class="flex items-center justify-between py-3.5">
                        <div>
                            <p class="text-sm font-semibold text-ink-800">{{ $pref['label'] }}</p>
                            <p class="text-xs text-ink-400 mt-0.5">{{ $pref['desc'] }}</p>
                        </div>
                        <div x-data="{ on: {{ $i < 3 ? 'true' : 'false' }} }">
                            <button type="button" x-on:click="on = !on" :class="on ? 'bg-brand-600' : 'bg-ink-200'" class="w-11 h-6 rounded-full relative transition-colors" role="switch" :aria-checked="on">
                                <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all" :class="on ? 'start-[22px]' : 'start-0.5'"></span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- اللغة -->
        <div x-show="tab === 'language'" class="card p-6">
            <h3 class="font-bold text-ink-800 mb-5">اللغة والمنطقة</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <button type="button" class="rounded-xl border-2 border-brand-500 bg-brand-50 p-4 text-center" data-toast="اللغة العربية مفعّلة">
                    <p class="font-bold text-ink-800">العربية</p>
                    <p class="text-xs text-brand-600 mt-1">مفعّلة</p>
                </button>
                <button type="button" class="rounded-xl border border-ink-200 p-4 text-center hover:border-ink-300" data-toast="سيتم دعم الفرنسية قريباً">
                    <p class="font-bold text-ink-700">Français</p>
                    <p class="text-xs text-ink-400 mt-1">قريباً</p>
                </button>
                <button type="button" class="rounded-xl border border-ink-200 p-4 text-center hover:border-ink-300" data-toast="سيتم دعم الإنجليزية قريباً">
                    <p class="font-bold text-ink-700">English</p>
                    <p class="text-xs text-ink-400 mt-1">قريباً</p>
                </button>
            </div>
        </div>

        <!-- المظهر -->
        <div x-show="tab === 'appearance'" class="card p-6">
            <h3 class="font-bold text-ink-800 mb-5">المظهر</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <button type="button" class="rounded-xl border-2 border-brand-500 p-3 text-center" data-toast="تم اختيار المظهر الفاتح">
                    <div class="h-14 rounded-lg bg-white border border-ink-200 mb-2"></div>
                    <p class="text-sm font-semibold text-ink-800">فاتح</p>
                </button>
                <button type="button" class="rounded-xl border border-ink-200 p-3 text-center hover:border-ink-300" data-toast="سيتم دعم المظهر الداكن قريباً">
                    <div class="h-14 rounded-lg bg-ink-900 mb-2"></div>
                    <p class="text-sm font-semibold text-ink-700">داكن (قريباً)</p>
                </button>
                <button type="button" class="rounded-xl border border-ink-200 p-3 text-center hover:border-ink-300" data-toast="سيتم دعم المظهر التلقائي قريباً">
                    <div class="h-14 rounded-lg bg-gradient-to-br from-white to-ink-900 mb-2"></div>
                    <p class="text-sm font-semibold text-ink-700">تلقائي (قريباً)</p>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
