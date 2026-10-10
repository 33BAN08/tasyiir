@php
    $wa = \App\Support\Demo::whatsappUrl(__('السلام عليكم، انتهت التجربة ديال TASYIIR (مركز :name) وبغيت النسخة الكاملة.', ['name' => $tenant->name]));
    $title = __('انتهت الفترة التجريبية');
@endphp

@component('layouts.guest', ['maxWidth' => 'max-w-md', 'title' => $title])
    <div class="text-center">
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-brand-50 text-brand-600 mx-auto mb-4">
            <x-icon name="timer" class="w-7 h-7" />
        </span>
        <h1 class="text-lg font-bold text-ink-900 mb-2">{{ $title }}</h1>
        <p class="text-sm text-ink-500 mb-1">{{ __('شكراً لتجربتك TASYIIR مع «:name».', ['name' => $tenant->name]) }}</p>
        <p class="text-sm text-ink-500 mb-6">{{ __('احصل على النسخة الكاملة على حاسوب مركزك: بدون إنترنت، بدون اشتراك شهري، وبياناتك تبقى عندك.') }}</p>

        <div class="rounded-xl bg-brand-50 border border-brand-100 p-4 mb-6">
            <p class="text-xs font-semibold text-brand-700 mb-1">{{ __('عرض الإطلاق') }}</p>
            <p class="text-2xl font-extrabold text-brand-700 ltr-nums">{{ config('tasyiir.demo.price') }}</p>
            <p class="text-xs text-brand-700/80">{{ __('مدى الحياة — التثبيت والمساعدة مجاناً') }}</p>
        </div>

        @if ($wa)
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn-primary w-full justify-center mb-3">
                <x-icon name="message-circle" class="w-4 h-4" /> {{ __('تواصل معنا على واتساب') }}
            </a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-secondary w-full justify-center">{{ __('تسجيل الخروج') }}</button>
        </form>
    </div>
@endcomponent
