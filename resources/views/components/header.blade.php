@php
    $notifications = [
        ['title' => 'تم تسجيل طالب جديد', 'time' => 'منذ 10 دقائق', 'icon' => 'user-round-plus', 'tone' => 'brand'],
        ['title' => 'تم استلام دفعة جديدة', 'time' => 'منذ 32 دقيقة', 'icon' => 'wallet', 'tone' => 'blue'],
        ['title' => 'لديك 3 طلاب لم يؤدوا رسوم هذا الشهر', 'time' => 'منذ 3 ساعات', 'icon' => 'triangle-alert', 'tone' => 'amber'],
        ['title' => 'حصة English B1 تبدأ بعد 30 دقيقة', 'time' => 'منذ 5 ساعات', 'icon' => 'calendar-check', 'tone' => 'violet'],
    ];
    $toneClasses = [
        'brand' => 'bg-brand-50 text-brand-600', 'blue' => 'bg-blue-50 text-blue-600',
        'amber' => 'bg-amber-50 text-amber-600', 'violet' => 'bg-violet-50 text-violet-600',
    ];
@endphp
<header class="sticky top-0 z-30 h-16 sm:h-20 flex items-center gap-3 bg-white/80 backdrop-blur border-b border-ink-100 px-4 sm:px-6">
    <button type="button" class="btn-icon lg:hidden" x-on:click="$store.ui.sidebarOpen = true" aria-label="فتح القائمة">
        <x-icon name="menu" class="w-5 h-5" />
    </button>

    <button
        type="button"
        x-on:click="$dispatch('open-search-modal')"
        class="hidden sm:flex items-center gap-2.5 w-full max-w-sm rounded-xl border border-ink-200 bg-ink-50 px-3.5 py-2.5 text-sm text-ink-400 hover:border-ink-300 hover:bg-white transition-colors focus-ring"
    >
        <x-icon name="search" class="w-4 h-4" />
        <span class="flex-1 text-start">بحث عن طالب، دورة، أستاذ...</span>
        <kbd class="ltr-nums text-[10px] font-mono border border-ink-200 rounded px-1.5 py-0.5 bg-white">Ctrl K</kbd>
    </button>

    <button type="button" class="btn-icon sm:hidden ms-auto" x-on:click="$dispatch('open-search-modal')" aria-label="بحث">
        <x-icon name="search" class="w-5 h-5" />
    </button>

    <div class="flex items-center gap-1.5 sm:gap-2 ms-auto">
        <a href="/students" data-toast="فتح نموذج إضافة طالب (تجريبي)" class="btn-primary hidden md:inline-flex">
            <x-icon name="plus" class="w-4 h-4" />
            إضافة طالب
        </a>

        <!-- Notifications -->
        <div class="relative" x-data="{ open: false }">
            <button type="button" x-on:click="open = !open" class="btn-icon relative" aria-label="الإشعارات">
                <x-icon name="bell" class="w-5 h-5" />
                <span class="absolute top-1.5 end-1.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
            <x-dropdown-panel align="end" width="w-80">
                <div class="flex items-center justify-between px-3.5 pb-2 mb-1 border-b border-ink-100">
                    <p class="text-sm font-bold text-ink-800">الإشعارات</p>
                    <span class="text-xs font-semibold text-brand-600">4 جديدة</span>
                </div>
                <div class="max-h-80 overflow-y-auto">
                    @foreach ($notifications as $n)
                        <div class="flex items-start gap-3 px-3.5 py-2.5 hover:bg-ink-50">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg shrink-0 {{ $toneClasses[$n['tone']] }}">
                                <x-icon :name="$n['icon']" class="w-4 h-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm text-ink-700 leading-snug">{{ $n['title'] }}</p>
                                <p class="text-xs text-ink-400 mt-0.5">{{ $n['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-3.5 pt-2 mt-1 border-t border-ink-100">
                    <a href="/notifications" class="block text-center text-sm font-semibold text-brand-600 hover:text-brand-700 py-1">عرض كل الإشعارات</a>
                </div>
            </x-dropdown-panel>
        </div>

        <div class="w-px h-6 bg-ink-200 hidden sm:block"></div>

        <!-- User menu -->
        <div class="relative" x-data="{ open: false }">
            <button type="button" x-on:click="open = !open" class="flex items-center gap-2.5 rounded-xl px-1.5 py-1 hover:bg-ink-100 transition-colors">
                <x-avatar name="محمد" size="sm" />
                <span class="hidden sm:flex flex-col items-start leading-tight">
                    <span class="text-sm font-semibold text-ink-800">محمد</span>
                    <span class="text-xs text-ink-400">مدير المركز</span>
                </span>
                <x-icon name="chevron-down" class="w-4 h-4 text-ink-400 hidden sm:block" />
            </button>
            <x-dropdown-panel align="end" width="w-52">
                <x-menu-item icon="user-round-plus" href="/settings">الملف الشخصي</x-menu-item>
                <x-menu-item icon="settings" href="/settings">الإعدادات</x-menu-item>
                <div class="my-1 border-t border-ink-100"></div>
                <x-menu-item icon="log-out" :danger="true">تسجيل الخروج</x-menu-item>
            </x-dropdown-panel>
        </div>
    </div>
</header>
