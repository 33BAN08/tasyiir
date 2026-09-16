@php
    $href = $href ?? null;
    $icon = $icon ?? null;
    $danger = $danger ?? false;
    $tag = $href ? 'a' : 'button';
    $toneClass = $danger ? 'text-red-600 hover:bg-red-50' : 'text-ink-600 hover:bg-ink-50';
@endphp
<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="button" @endif
    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-medium transition-colors {{ $toneClass }}"
>
    @if ($icon)
        <x-icon :name="$icon" class="w-4 h-4" />
    @endif
    <span>{{ $slot }}</span>
</{{ $tag }}>
