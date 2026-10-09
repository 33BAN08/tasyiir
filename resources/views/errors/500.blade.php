@php $rtl = ! in_array(app()->getLocale(), ['fr', 'en'], true); @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('خطأ غير متوقع') }} · TASYIIR</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css'])
</head>
{{-- Standalone on purpose: the app layout needs a working database/session,
     which is exactly what may be broken when this page is shown. --}}
<body class="h-full bg-ink-50 font-sans text-ink-800 antialiased">
    <div class="min-h-full flex flex-col items-center justify-center px-4 py-10 text-center">
        <span class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-red-50 text-red-600 mb-5">
            <x-icon name="server-crash" class="w-7 h-7" />
        </span>
        <h1 class="text-xl font-bold text-ink-900 mb-2">{{ __('حدث خطأ غير متوقع') }}</h1>
        <p class="text-sm text-ink-500 max-w-md mb-6">
            {{ __('لم يتمكن البرنامج من إتمام هذه العملية. بياناتك لم تتأثر. أعد المحاولة، وإذا تكرر الخطأ تواصل مع IAM Agency.') }}
        </p>
        <div class="flex items-center gap-2">
            <a href="/dashboard" class="btn-primary">{{ __('العودة إلى الرئيسية') }}</a>
            <button type="button" class="btn-secondary" onclick="location.reload()">{{ __('إعادة المحاولة') }}</button>
        </div>
        <p class="mt-8 text-xs text-ink-400">© {{ date('Y') }} TASYIIR — {{ __('صُنع بواسطة IAM Agency') }}</p>
    </div>
</body>
</html>
