<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#059669" />
    <title>@yield('title', $title ?? 'الرئيسية') · TASYIIR</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @if (session('toast'))
        <script>window.__flashToast = @json(session('toast'));</script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-ink-50 font-sans text-ink-800 antialiased" x-data>

    <x-sidebar />

    <div class="lg:ps-72 min-h-full flex flex-col">
        <x-header />

        <main class="flex-1 w-full max-w-[1600px] mx-auto p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>

        <footer class="px-4 sm:px-6 lg:p-8 pb-6 text-center text-xs text-ink-400">
            © {{ date('Y') }} <span class="font-semibold text-ink-500">TASYIIR</span> — {{ auth()->user()?->tenant?->name }}. جميع الحقوق محفوظة.
            <span class="block sm:inline sm:ms-2">صُنع بواسطة <span class="font-semibold text-ink-500">IAM Agency</span></span>
        </footer>
    </div>

    <x-toast-container />

    <x-modal id="search-modal" title="البحث الشامل" max-width="lg">
        <div class="relative mb-4">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400"><x-icon name="search" class="w-4 h-4" /></span>
            <input type="text" autofocus class="input" placeholder="ابحث عن طالب، أستاذ، دورة، مجموعة..." />
        </div>
        <p class="text-xs font-semibold text-ink-400 mb-2">روابط سريعة</p>
        <div class="space-y-1">
            <x-menu-item icon="users" href="/students">الطلاب</x-menu-item>
            <x-menu-item icon="graduation-cap" href="/teachers">الأساتذة</x-menu-item>
            <x-menu-item icon="book-open" href="/courses">الدورات</x-menu-item>
            <x-menu-item icon="calendar-days" href="/schedule">الجدول</x-menu-item>
            <x-menu-item icon="wallet" href="/payments">أداءات الطلاب</x-menu-item>
        </div>
    </x-modal>

    <script defer src="/vendor/chart.js"></script>
    @livewireScripts
    @yield('scripts')
</body>
</html>
