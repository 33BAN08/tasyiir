@php
    $icon = $icon ?? 'inbox';
    $title = $title ?? __('لا توجد بيانات');
    $description = $description ?? __('لم يتم العثور على أي عناصر لعرضها هنا حالياً.');
@endphp
<div class="flex flex-col items-center justify-center text-center py-14 px-6">
    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-ink-100 text-ink-400 mb-4">
        <x-icon :name="$icon" class="w-6 h-6" />
    </span>
    <p class="text-sm font-semibold text-ink-700">{{ $title }}</p>
    <p class="text-sm text-ink-400 mt-1 max-w-sm">{{ $description }}</p>
    @if (!empty(trim($slot)))
        <div class="mt-4">{!! $slot !!}</div>
    @endif
</div>
