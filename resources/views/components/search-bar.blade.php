@php
    $placeholder = $placeholder ?? __('بحث...');
@endphp
<div class="relative flex-1 min-w-[200px]">
    <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400 pointer-events-none">
        <x-icon name="search" class="w-4 h-4" />
    </span>
    <input
        type="text"
        class="input"
        placeholder="{{ $placeholder }}"
        aria-label="{{ $placeholder }}"
    />
</div>
