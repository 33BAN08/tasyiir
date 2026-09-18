<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#059669" />
    <title>{{ $title ?? 'تسجيل الدخول' }} · TASYIIR</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-ink-50 font-sans text-ink-800 antialiased">
    <div class="min-h-full flex flex-col items-center justify-center px-4 py-10">
        <div class="mb-8 flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-brand-600 text-white font-extrabold text-xl shrink-0">T</span>
            <div class="leading-tight text-start">
                <p class="font-extrabold text-xl tracking-wide text-ink-900">TASYIIR</p>
                <p class="text-[11px] text-ink-500">منصة تسيير مراكز التكوين</p>
            </div>
        </div>

        <div class="w-full {{ $maxWidth ?? 'max-w-sm' }} card p-6 sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-xs text-ink-400">© {{ date('Y') }} TASYIIR — جميع الحقوق محفوظة. صُنع بواسطة IAM Agency</p>
    </div>

    <x-toast-container />
    @livewireScripts
</body>
</html>
