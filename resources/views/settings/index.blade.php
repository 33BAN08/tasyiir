@extends('layouts.app')

@section('title', __('الإعدادات'))

@section('content')

<x-page-header title="{{ __('الإعدادات') }}" subtitle="{{ __('إدارة إعدادات المركز والحساب والنظام') }}" />

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6" x-data="{ tab: '{{ $openTab }}' }">
    <!-- Side nav -->
    <div class="lg:col-span-1">
        <div class="card p-2 flex lg:flex-col gap-1 overflow-x-auto">
            @foreach ([
                ['key' => 'center', 'label' => __('معلومات المركز'), 'icon' => 'building-2'],
                ['key' => 'account', 'label' => __('الحساب'), 'icon' => 'user-round-plus'],
                ['key' => 'users', 'label' => __('المستخدمون'), 'icon' => 'users', 'can' => 'manage-users'],
                ['key' => 'roles', 'label' => __('الصلاحيات'), 'icon' => 'shield-check', 'can' => 'manage-users'],
                ['key' => 'backup', 'label' => __('النسخ الاحتياطي'), 'icon' => 'download', 'can' => 'manage-settings'],
                ['key' => 'license', 'label' => __('الترخيص'), 'icon' => 'shield-check', 'can' => 'manage-settings', 'only' => 'local'],
                ['key' => 'notifications', 'label' => __('الإشعارات'), 'icon' => 'bell'],
                ['key' => 'language', 'label' => __('اللغة'), 'icon' => 'languages'],
                ['key' => 'appearance', 'label' => __('المظهر'), 'icon' => 'palette'],
            ] as $item)
                @continue (isset($item['can']) && ! auth()->user()->can($item['can']))
                @continue (($item['only'] ?? null) === 'local' && ! \App\Support\Mode::isLocal())
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

        @can('manage-settings')
            <!-- النسخ الاحتياطي -->
            <div x-show="tab === 'backup'" class="card p-6">
                <livewire:settings.backup />
            </div>

            @if (\App\Support\Mode::isLocal())
                <!-- الترخيص -->
                <div x-show="tab === 'license'" class="card p-6">
                    <livewire:settings.license />
                </div>
            @endif
        @endcan

        <!-- الإشعارات -->
        <div x-show="tab === 'notifications'" class="card p-6">
            <h3 class="font-bold text-ink-800 mb-5">{{ __('تفضيلات الإشعارات') }}</h3>
            <div class="divide-y divide-ink-100">
                @foreach ([
                    ['label' => __('تسجيل طالب جديد'), 'desc' => __('إشعار عند إضافة طالب جديد إلى النظام')],
                    ['label' => __('استلام دفعة'), 'desc' => __('إشعار عند تسجيل دفعة جديدة من طالب')],
                    ['label' => __('الطلاب غير المؤدين'), 'desc' => __('تذكير أسبوعي بالطلاب المتأخرين عن الدفع')],
                    ['label' => __('الحصص القادمة'), 'desc' => __('تنبيه قبل بداية كل حصة بـ 30 دقيقة')],
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
            <h3 class="font-bold text-ink-800 mb-5">{{ __('اللغة والمنطقة') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach ([
                    'ar' => 'العربية',
                    'fr' => 'Français',
                    'en' => 'English',
                ] as $code => $label)
                    <form method="POST" action="{{ route('settings.language') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button
                            type="submit"
                            @class([
                                'w-full rounded-xl border-2 p-4 text-center',
                                'border-brand-500 bg-brand-50' => app()->getLocale() === $code,
                                'border-ink-200 hover:border-ink-300' => app()->getLocale() !== $code,
                            ])
                        >
                            <p @class(['font-bold', 'text-ink-800' => app()->getLocale() === $code, 'text-ink-700' => app()->getLocale() !== $code])>
                                {{ $label }}
                            </p>
                            <p class="text-xs mt-1 {{ app()->getLocale() === $code ? 'text-brand-600' : 'text-ink-400' }}">
                                {{ app()->getLocale() === $code ? __('مفعّلة') : __('اضغط للتفعيل') }}
                            </p>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <!-- المظهر -->
        <div x-show="tab === 'appearance'" class="card p-6">
            <h3 class="font-bold text-ink-800 mb-5">{{ __('المظهر') }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <button type="button" class="rounded-xl border-2 border-brand-500 p-3 text-center" data-toast="{{ __('تم اختيار المظهر الفاتح') }}">
                    <div class="h-14 rounded-lg bg-white border border-ink-200 mb-2"></div>
                    <p class="text-sm font-semibold text-ink-800">{{ __('فاتح') }}</p>
                </button>
                <button type="button" class="rounded-xl border border-ink-200 p-3 text-center hover:border-ink-300" data-toast="{{ __('سيتم دعم المظهر الداكن قريباً') }}">
                    <div class="h-14 rounded-lg bg-ink-900 mb-2"></div>
                    <p class="text-sm font-semibold text-ink-700">{{ __('داكن (قريباً)') }}</p>
                </button>
                <button type="button" class="rounded-xl border border-ink-200 p-3 text-center hover:border-ink-300" data-toast="{{ __('سيتم دعم المظهر التلقائي قريباً') }}">
                    <div class="h-14 rounded-lg bg-gradient-to-br from-white to-ink-900 mb-2"></div>
                    <p class="text-sm font-semibold text-ink-700">{{ __('تلقائي (قريباً)') }}</p>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
