@php
    $title = $title ?? __('حدث خطأ غير متوقع');
    $description = $description ?? __('تعذر تحميل البيانات. يرجى إعادة المحاولة.');
@endphp
<div class="flex flex-col items-center justify-center text-center py-14 px-6">
    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-red-50 text-red-500 mb-4">
        <x-icon name="server-crash" class="w-6 h-6" />
    </span>
    <p class="text-sm font-semibold text-ink-700">{{ $title }}</p>
    <p class="text-sm text-ink-400 mt-1 max-w-sm">{{ $description }}</p>
    <button type="button" class="btn-secondary mt-4" data-toast="{{ __('تمت إعادة المحاولة') }}" onclick="location.reload()">
        <x-icon name="loader-circle" class="w-4 h-4" />
        {{ __('إعادة المحاولة') }}
    </button>
</div>
