@php
    $align = $align ?? 'end'; // start | end
    $width = $width ?? 'w-56';
    $alignClass = $align === 'start' ? 'start-0' : 'end-0';
@endphp
<div
    x-show="open"
    x-cloak
    x-transition:enter="ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-on:click.outside="open = false"
    style="display:none;"
    class="absolute {{ $alignClass }} top-full mt-2 {{ $width }} z-40 rounded-xl bg-white border border-ink-100 shadow-popover py-1.5 origin-top-{{ $align === 'start' ? 'left' : 'right' }}"
>
    {!! $slot !!}
</div>
