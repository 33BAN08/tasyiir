<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#059669" />
    <title>@yield('title', $title ?? 'لوحة المنصة') · TASYIIR</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
{{-- The operator's back office: no tenant sidebar, no tenant widgets. --}}
<body class="h-full bg-ink-50 font-sans text-ink-800 antialiased">
    <header class="bg-ink-950 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-brand-500 text-white font-extrabold shrink-0">T</span>
            <div class="leading-tight min-w-0">
                <p class="font-extrabold truncate">TASYIIR <span class="text-ink-400 font-semibold">· لوحة المنصة</span></p>
                <p class="text-[11px] text-ink-400 truncate">{{ auth()->user()?->name }} — مسؤول المنصة</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="ms-auto">
                @csrf
                <button type="submit" class="btn-icon text-ink-300 hover:text-white hover:bg-white/10" aria-label="تسجيل الخروج" title="تسجيل الخروج">
                    <x-icon name="log-out" class="w-[18px] h-[18px]" />
                </button>
            </form>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        @yield('content')
    </main>

    <footer class="max-w-6xl mx-auto px-4 sm:px-6 py-6 text-xs text-ink-400">© {{ date('Y') }} TASYIIR — صُنع بواسطة IAM Agency</footer>

    <x-toast-container />
    @livewireScripts
    @if (session('toast'))
        <script>window.__flashToast = @json(session('toast'));</script>
    @endif
</body>
</html>
