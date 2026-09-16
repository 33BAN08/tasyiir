@php
    $tone = $tone ?? 'neutral';
    $dot = $dot ?? '1';

    $tones = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 [--dot:theme(colors.emerald.500)]',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20 [--dot:theme(colors.amber.500)]',
        'danger'  => 'bg-red-50 text-red-700 ring-red-600/20 [--dot:theme(colors.red.500)]',
        'info'    => 'bg-blue-50 text-blue-700 ring-blue-600/20 [--dot:theme(colors.blue.500)]',
        'violet'  => 'bg-violet-50 text-violet-700 ring-violet-600/20 [--dot:theme(colors.violet.500)]',
        'neutral' => 'bg-ink-100 text-ink-600 ring-ink-500/15 [--dot:theme(colors.ink.400)]',
    ];
    $classes = $tones[$tone] ?? $tones['neutral'];
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $classes }}">
    @if ($dot === '1' || $dot === 1 || $dot === true)
        <span class="w-1.5 h-1.5 rounded-full" style="background-color: var(--dot)"></span>
    @endif
    {{ $label }}
</span>
