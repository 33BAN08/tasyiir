@php
    $current = (int) ($current ?? 1);
    $last = (int) ($last ?? 1);
    $total = $total ?? null;
    $from = $from ?? null;
    $to = $to ?? null;

    $pages = [];
    $start = max(1, $current - 1);
    $end = min($last, $current + 1);
    for ($p = $start; $p <= $end; $p++) { $pages[] = $p; }
@endphp
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3.5 border-t border-ink-100">
    @if ($total !== null)
        <p class="text-sm text-ink-500 order-2 sm:order-1">
            عرض <span class="ltr-nums font-semibold text-ink-700">{{ $from }}–{{ $to }}</span>
            من <span class="ltr-nums font-semibold text-ink-700">{{ $total }}</span> نتيجة
        </p>
    @endif

    <nav class="flex items-center gap-1 order-1 sm:order-2" aria-label="ترقيم الصفحات">
        <button type="button" class="btn-icon" {{ $current <= 1 ? 'disabled' : '' }} aria-label="السابق">
            <x-icon name="chevron-right" class="w-4 h-4" />
        </button>

        @if ($start > 1)
            <button type="button" class="min-w-[2.25rem] h-9 rounded-lg text-sm font-medium text-ink-500 hover:bg-ink-100">1</button>
            @if ($start > 2)
                <span class="px-1 text-ink-400">…</span>
            @endif
        @endif

        @foreach ($pages as $p)
            <button
                type="button"
                class="ltr-nums min-w-[2.25rem] h-9 rounded-lg text-sm font-semibold {{ $p === $current ? 'bg-brand-600 text-white' : 'text-ink-600 hover:bg-ink-100' }}"
            >{{ $p }}</button>
        @endforeach

        @if ($end < $last)
            @if ($end < $last - 1)
                <span class="px-1 text-ink-400">…</span>
            @endif
            <button type="button" class="min-w-[2.25rem] h-9 rounded-lg text-sm font-medium text-ink-500 hover:bg-ink-100">{{ $last }}</button>
        @endif

        <button type="button" class="btn-icon" {{ $current >= $last ? 'disabled' : '' }} aria-label="التالي">
            <x-icon name="chevron-left" class="w-4 h-4" />
        </button>
    </nav>
</div>
