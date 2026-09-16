@php $subtitle = $subtitle ?? null; @endphp
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-ink-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-sm text-ink-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if (!empty(trim($slot)))
        <div class="flex items-center gap-2 flex-wrap">
            {!! $slot !!}
        </div>
    @endif
</div>
