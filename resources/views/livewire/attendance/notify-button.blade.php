<div class="flex flex-wrap items-center justify-end gap-2">
    @if ($record->notified_at)
        <span class="text-[11px] text-emerald-700 whitespace-nowrap">
            ✓ {{ __('تم الإشعار') }}
            <span class="ltr-nums">{{ $record->notified_at->format('H:i') }}</span>
            @if ($record->notifier) — {{ $record->notifier->name }} @endif
        </span>
    @endif

    @if (! $canNotify)
        {{-- Read-only roles see the state, never a way to message a parent. --}}
    @elseif ($link)
        <button type="button"
                class="btn-icon"
                title="{{ __('نسخ النص') }}"
                aria-label="{{ __('نسخ النص') }}"
                x-data="{ done: false }"
                x-on:click="window.copyText(@js($message)).then(ok => { done = ok; setTimeout(() => done = false, 1500) })">
            {{-- No x-cloak rule in this app's CSS, so the second icon starts hidden. --}}
            <x-icon name="copy" class="w-4 h-4" x-show="!done" />
            <x-icon name="check" class="w-4 h-4 text-emerald-600" style="display:none" x-show="done" />
        </button>

        <a href="{{ $link }}"
           target="_blank"
           rel="noopener"
           x-on:click="$wire.markNotified()"
           class="{{ $record->notified_at ? 'btn-secondary' : 'btn-primary bg-emerald-600 hover:bg-emerald-700 border-emerald-600' }} whitespace-nowrap">
            <x-icon name="message-circle" class="w-4 h-4" />
            {{ $record->notified_at ? __('إعادة الإرسال') : __('إشعار واتساب') }}
        </a>

        @unless ($isGuardian)
            <span class="text-[11px] text-amber-700 whitespace-nowrap">{{ __('رقم الطالب') }}</span>
        @endunless
    @else
        <button type="button" class="btn-secondary opacity-50 cursor-not-allowed whitespace-nowrap" disabled
                title="{{ __('لا يوجد رقم هاتف صالح') }}">
            <x-icon name="message-circle" class="w-4 h-4" />
            {{ __('إشعار واتساب') }}
        </button>
        @if ($record->student)
            <a href="{{ route('students.index', ['edit' => $record->student->id]) }}"
               class="text-[11px] font-semibold text-brand-600 hover:text-brand-700 whitespace-nowrap">
                {{ __('إضافة رقم') }}
            </a>
        @endif
    @endif
</div>
