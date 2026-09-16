@php
    $id = $id ?? 'modal';
    $title = $title ?? '';
    $maxWidth = $max_width ?? 'md';
    $widths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl', '2xl' => 'max-w-2xl'];
@endphp
<div
    x-data="{ open: false }"
    x-on:open-{{ $id }}.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display: none;"
>
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-ink-950/50"
        x-on:click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full {{ $widths[$maxWidth] ?? $widths['md'] }} bg-white rounded-2xl shadow-popover overflow-hidden"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
            <h2 class="text-base font-bold text-ink-900">{{ $title }}</h2>
            <button type="button" class="btn-icon" x-on:click="open = false" aria-label="إغلاق">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>
        <div class="p-5 max-h-[70vh] overflow-y-auto">
            {!! $slot !!}
        </div>
    </div>
</div>
