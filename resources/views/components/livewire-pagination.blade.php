@props(['paginator'])
{{-- Same markup as the Phase 1 x-pagination component, wired to the enclosing Livewire component's WithPagination methods. --}}
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3.5 border-t border-ink-100">
    <p class="text-sm text-ink-500 order-2 sm:order-1">
        {{ __('عرض') }} <span class="ltr-nums font-semibold text-ink-700">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
        {{ __('من') }} <span class="ltr-nums font-semibold text-ink-700">{{ $paginator->total() }}</span> {{ __('نتيجة') }}
    </p>
    <nav class="flex items-center gap-1 order-1 sm:order-2" aria-label="{{ __('ترقيم الصفحات') }}">
        <button type="button" class="btn-icon" wire:click="previousPage" @if ($paginator->onFirstPage()) disabled @endif aria-label="{{ __('السابق') }}">
            <x-icon name="chevron-right" class="ltr:rotate-180 w-4 h-4" />
        </button>
        @for ($p = 1; $p <= $paginator->lastPage(); $p++)
            <button type="button" wire:click="gotoPage({{ $p }})"
                class="ltr-nums min-w-[2.25rem] h-9 rounded-lg text-sm font-semibold {{ $p === $paginator->currentPage() ? 'bg-brand-600 text-white' : 'text-ink-600 hover:bg-ink-100' }}">{{ $p }}</button>
        @endfor
        <button type="button" class="btn-icon" wire:click="nextPage" @if (!$paginator->hasMorePages()) disabled @endif aria-label="{{ __('التالي') }}">
            <x-icon name="chevron-left" class="ltr:rotate-180 w-4 h-4" />
        </button>
    </nav>
</div>
