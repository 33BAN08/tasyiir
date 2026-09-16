@php
    $name = $name ?? '';
    $size = $size ?? 'md';
    $sizes = [
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-14 h-14 text-base',
        'xl' => 'w-20 h-20 text-lg',
    ];
    $palette = ['bg-emerald-100 text-emerald-700', 'bg-blue-100 text-blue-700', 'bg-violet-100 text-violet-700', 'bg-amber-100 text-amber-700', 'bg-rose-100 text-rose-700', 'bg-teal-100 text-teal-700'];
    $hash = 0;
    foreach (str_split($name) as $ch) { $hash += mb_ord($ch) ?: 0; }
    $color = $palette[$hash % count($palette)];
@endphp
<span class="inline-flex items-center justify-center shrink-0 rounded-full font-semibold {{ $sizes[$size] ?? $sizes['md'] }} {{ $color }}">
    {{ initials($name) }}
</span>
