@php
    $tone = $tone ?? 'brand';
    $href = $href ?? '#';
    $tones = [
        'brand'  => 'bg-brand-50 text-brand-600 group-hover:bg-brand-600 group-hover:text-white',
        'blue'   => 'bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white',
        'violet' => 'bg-violet-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white',
        'amber'  => 'bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white',
        'rose'   => 'bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white',
        'teal'   => 'bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white',
    ];
@endphp
<a
    href="{{ $href }}"
    data-toast="{{ $toast ?? '' }}"
    class="group card p-4 flex flex-col items-center justify-center text-center gap-2.5 hover:border-brand-300 hover:shadow-soft transition-all"
>
    <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl transition-colors {{ $tones[$tone] ?? $tones['brand'] }}">
        <x-icon :name="$icon" class="w-5 h-5" />
    </span>
    <span class="text-sm font-semibold text-ink-700">{{ $label }}</span>
</a>
