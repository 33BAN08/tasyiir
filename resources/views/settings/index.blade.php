@extends('layouts.app')

@section('title', 'الإعدادات')

@section('content')
@php
    $users = [
        ['name' => 'محمد', 'role' => 'مدير المركز', 'email' => 'mohamed@planzeen.ma', 'status' => 'نشط'],
        ['name' => 'أستاذ يوسف الفاسي', 'role' => 'أستاذ', 'email' => 'y.fassi@planzeen.ma', 'status' => 'نشط'],
        ['name' => 'سعاد بنعمر', 'role' => 'إدارية استقبال', 'email' => 's.benomar@planzeen.ma', 'status' => 'نشط'],
        ['name' => 'أستاذة مريم الغازي', 'role' => 'أستاذة', 'email' => 'm.ghazi@planzeen.ma', 'status' => 'متوقف'],
    ];
    $roles = [
        ['name' => 'مدير المركز', 'desc' => 'صلاحية كاملة على جميع وحدات النظام', 'count' => 1],
        ['name' => 'إدارية / استقبال', 'desc' => 'الطلاب، التسجيلات، المدفوعات، الحضور', 'count' => 2],
        ['name' => 'أستاذ', 'desc' => 'الحضور والجدول الخاص بمجموعاته فقط', 'count' => 15],
        ['name' => 'محاسب', 'desc' => 'المصاريف، أجور الأساتذة، التقارير المالية', 'count' => 0],
    ];
    $statusTone = ['نشط' => 'success', 'متوقف' => 'neutral'];
@endphp

<x-page-header title="الإعدادات" subtitle="إدارة إعدادات المركز والحساب والنظام" />

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6" x-data="{ tab: 'center' }">
    <!-- Side nav -->
    <div class="lg:col-span-1">
        <div class="card p-2 flex lg:flex-col gap-1 overflow-x-auto">
            @foreach ([
                ['key' => 'center', 'label' => 'معلومات المركز', 'icon' => 'building-2'],
                ['key' => 'account', 'label' => 'الحساب', 'icon' => 'user-round-plus'],
                ['key' => 'users', 'label' => 'المستخدمون', 'icon' => 'users'],
                ['key' => 'roles', 'label' => 'الصلاحيات', 'icon' => 'shield-check'],
                ['key' => 'notifications', 'label' => 'الإشعارات', 'icon' => 'bell'],
                ['key' => 'language', 'label' => 'اللغة', 'icon' => 'languages'],
                ['key' => 'appearance', 'label' => 'المظهر', 'icon' => 'palette'],
            ] as $item)
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
            <h3 class="font-bold text-ink-800 mb-5">معلومات المركز</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1.5">اسم المركز</label>
                    <input type="text" class="input ps-3" value="مركز النجاح للتكوين" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1.5">رقم الهاتف</label>
                    <input type="text" class="input ps-3 ltr-nums" value="0522-11-22-33" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1.5">البريد الإلكتروني</label>
                    <input type="email" class="input ps-3" value="contact@najah-center.ma" />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700 mb-1.5">العنوان</label>
                    <input type="text" class="input ps-3" value="شارع الحسن الثاني، الدار البيضاء" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1.5">العملة</label>
                    <select class="select"><option>درهم مغربي (MAD)</option></select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700 mb-1.5">التوقيت</label>
                    <select class="select"><option>(GMT+1) الدار البيضاء</option></select>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="btn-primary" data-toast="تم حفظ معلومات المركز (تجريبي)"><x-icon name="check" class="w-4 h-4" /> حفظ التغييرات</button>
            </div>
        </div>

        <!-- الحساب -->
        <div x-show="tab === 'account'" class="card p-6">
            <h3 class="font-bold text-ink-800 mb-5">الحساب الشخصي</h3>
            <div class="flex items-center gap-4 mb-6">
                <x-avatar name="محمد" size="xl" />
                <div>
                    <button type="button" class="btn-secondary" data-toast="تم تغيير الصورة (تجريبي)">تغيير الصورة</button>
                    <p class="text-xs text-ink-400 mt-1.5">JPG أو PNG بحجم أقصى 2MB</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-ink-700 mb-1.5">الاسم الكامل</label><input type="text" class="input ps-3" value="محمد" /></div>
                <div><label class="block text-sm font-medium text-ink-700 mb-1.5">المنصب</label><input type="text" class="input ps-3" value="مدير المركز" /></div>
                <div><label class="block text-sm font-medium text-ink-700 mb-1.5">البريد الإلكتروني</label><input type="email" class="input ps-3" value="mohamed@planzeen.ma" /></div>
                <div><label class="block text-sm font-medium text-ink-700 mb-1.5">رقم الهاتف</label><input type="text" class="input ps-3 ltr-nums" value="0661-22-33-44" /></div>
                <div><label class="block text-sm font-medium text-ink-700 mb-1.5">كلمة المرور الجديدة</label><input type="password" class="input ps-3" placeholder="••••••••" /></div>
                <div><label class="block text-sm font-medium text-ink-700 mb-1.5">تأكيد كلمة المرور</label><input type="password" class="input ps-3" placeholder="••••••••" /></div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="btn-primary" data-toast="تم حفظ بيانات الحساب (تجريبي)"><x-icon name="check" class="w-4 h-4" /> حفظ التغييرات</button>
            </div>
        </div>

        <!-- المستخدمون -->
        <div x-show="tab === 'users'" class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-ink-100">
                <h3 class="font-bold text-ink-800">المستخدمون</h3>
                <button type="button" class="btn-primary" data-toast="فتح نموذج دعوة مستخدم (تجريبي)"><x-icon name="plus" class="w-4 h-4" /> إضافة مستخدم</button>
            </div>
            <div class="divide-y divide-ink-100">
                @foreach ($users as $u)
                    <div class="flex items-center gap-3 px-6 py-4">
                        <x-avatar :name="$u['name']" size="sm" />
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-ink-800 text-sm">{{ $u['name'] }}</p>
                            <p class="text-xs text-ink-400">{{ $u['role'] }} · {{ $u['email'] }}</p>
                        </div>
                        <x-status-badge :label="$u['status']" :tone="$statusTone[$u['status']] ?? 'neutral'" />
                        <button type="button" class="btn-icon" data-toast="فتح نموذج تعديل المستخدم (تجريبي)" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- الصلاحيات -->
        <div x-show="tab === 'roles'" class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-ink-100">
                <h3 class="font-bold text-ink-800">الأدوار والصلاحيات</h3>
                <button type="button" class="btn-secondary" data-toast="فتح نموذج إنشاء دور (تجريبي)"><x-icon name="plus" class="w-4 h-4" /> دور جديد</button>
            </div>
            <div class="divide-y divide-ink-100">
                @foreach ($roles as $r)
                    <div class="flex items-center gap-3 px-6 py-4">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-violet-50 text-violet-600 shrink-0">
                            <x-icon name="shield-check" class="w-5 h-5" />
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-ink-800 text-sm">{{ $r['name'] }}</p>
                            <p class="text-xs text-ink-400">{{ $r['desc'] }}</p>
                        </div>
                        <span class="ltr-nums text-xs font-semibold text-ink-500 shrink-0">{{ $r['count'] }} مستخدم</span>
                    </div>
                @endforeach
            </div>
        </div>

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
