@php
    $tone = $tone ?? 'brand';
    $trend = $trend ?? null; // numeric or null
    $trendLabel = $trendLabel ?? 'عن الشهر الماضي';

    $tones = [
        'brand'  => 'bg-brand-50 text-brand-600',
        'blue'   => 'bg-blue-50 text-blue-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'amber'  => 'bg-amber-50 text-amber-600',
        'rose'   => 'bg-rose-50 text-rose-600',
    ];
    $iconClasses = $tones[$tone] ?? $tones['brand'];
@endphp
<div class="card p-5 flex flex-col gap-4 animate-fade-in">
    <div class="flex items-center justify-between">
        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl {{ $iconClasses }}">
            <x-icon :name="$icon" class="w-5 h-5" />
        </span>
        @if ($trend !== null)
            <span class="ltr-nums inline-flex items-center gap-1 text-xs font-semibold {{ $trend >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                <x-icon :name="$trend >= 0 ? 'trending-up' : 'trending-down'" class="w-3.5 h-3.5" />
                {{ ($trend >= 0 ? '+' : '') . $trend }}%
            </span>
        @endif
    </div>
    <div>
        <p class="text-sm text-ink-500">{{ $label }}</p>
        <p class="text-2xl font-bold text-ink-900 mt-1">{{ $value }}</p>
    </div>
    @if ($trend !== null)
        <p class="text-xs text-ink-400">{{ $trendLabel }}</p>
    @endif
</div>
