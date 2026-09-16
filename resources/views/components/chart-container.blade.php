@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $id = $id ?? ('chart-' . uniqid());
    $height = $height ?? '280px';
@endphp
<div class="card p-5">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-sm font-bold text-ink-800">{{ $title }}</h3>
            @if ($subtitle)
                <p class="text-xs text-ink-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if (!empty(trim($slot)))
            <div class="flex items-center gap-2">{!! $slot !!}</div>
        @endif
    </div>
    <div style="height: {{ $height }}">
        <canvas id="{{ $id }}"></canvas>
    </div>
</div>
