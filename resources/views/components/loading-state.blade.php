@php $rows = (int) ($rows ?? 5); @endphp
<div class="p-4 space-y-3" role="status" aria-live="polite">
    @foreach (range(1, $rows) as $r)
        <div class="h-12 rounded-xl bg-ink-100 animate-pulse"></div>
    @endforeach
    <span class="sr-only">جارٍ التحميل...</span>
</div>
