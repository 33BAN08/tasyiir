<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#059669" />
    <title>{{ $title ?? __('تسجيل الدخول') }} · TASYIIR</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-ink-50 font-sans text-ink-800 antialiased">
    {{-- Language switcher: guests have no Settings page, so it lives here (login + center signup). end-4 keeps it in the visually-correct corner in both directions. --}}
    <div class="absolute top-4 end-4 flex items-center gap-1 rounded-full border border-ink-200 bg-white p-1 shadow-sm">
        @foreach (['ar' => 'ع', 'fr' => 'FR', 'en' => 'EN'] as $code => $short)
            <form method="POST" action="{{ route('locale.switch') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $code }}">
                <button
                    type="submit"
                    title="{{ ['ar' => 'العربية', 'fr' => 'Français', 'en' => 'English'][$code] }}"
                    @class([
                        'w-8 h-8 rounded-full text-xs font-bold transition-colors',
                        'bg-brand-600 text-white' => app()->getLocale() === $code,
                        'text-ink-500 hover:bg-ink-100' => app()->getLocale() !== $code,
                    ])
                >
                    {{ $short }}
                </button>
            </form>
        @endforeach
    </div>

    <div class="min-h-full flex flex-col items-center justify-center px-4 py-10">
        <div class="mb-8 flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-brand-600 text-white font-extrabold text-xl shrink-0">T</span>
            <div class="leading-tight text-start">
                <p class="font-extrabold text-xl tracking-wide text-ink-900">TASYIIR</p>
                <p class="text-[11px] text-ink-500">{{ __('منصة تسيير مراكز التكوين') }}</p>
            </div>
        </div>

        <div class="w-full {{ $maxWidth ?? 'max-w-sm' }} card p-6 sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-xs text-ink-400">© {{ date('Y') }} TASYIIR — {{ __('جميع الحقوق محفوظة') }}. {{ __('صُنع بواسطة IAM Agency') }}</p>
    </div>

    <x-toast-container />
    @livewireScripts
</body>
</html>
