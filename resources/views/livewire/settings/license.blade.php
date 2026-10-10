@php
    $tone = match ($status['state']) {
        \App\Services\License::LICENSED => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'icon' => 'circle-check', 'label' => __('مفعّل')],
        \App\Services\License::TRIAL => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'icon' => 'timer', 'label' => __('نسخة تجريبية')],
        \App\Services\License::UNSIGNED_BUILD => ['bg' => 'bg-ink-100', 'text' => 'text-ink-600', 'icon' => 'circle-dot', 'label' => __('نسخة تطوير')],
        default => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => 'triangle-alert', 'label' => __('غير مفعّل')],
    };
@endphp

<div class="space-y-6">
    <div>
        <h3 class="font-bold text-ink-800 mb-1">{{ __('الترخيص') }}</h3>
        <p class="text-xs text-ink-400">{{ __('نسخة البرنامج:') }} <span class="ltr-nums">{{ $version }}</span></p>
    </div>

    <div class="rounded-xl {{ $tone['bg'] }} p-4 flex items-start gap-3">
        <x-icon :name="$tone['icon']" class="w-5 h-5 shrink-0 {{ $tone['text'] }}" />
        <div class="min-w-0">
            <p class="font-semibold {{ $tone['text'] }}">{{ $tone['label'] }}</p>
            @if ($status['message'])
                <p class="text-sm text-ink-600 mt-0.5">{{ $status['message'] }}</p>
            @endif
            @if ($status['expires_at'])
                <p class="text-sm text-ink-600 mt-0.5">
                    {{ __('تاريخ الانتهاء:') }} <span class="ltr-nums font-semibold">{{ $status['expires_at']->format('Y-m-d') }}</span>
                    @if ($status['days_left'] !== null)
                        <span class="text-ink-400">({{ __('متبق :count يوماً', ['count' => $status['days_left']]) }})</span>
                    @endif
                </p>
            @elseif ($status['state'] === \App\Services\License::LICENSED)
                <p class="text-sm text-ink-600 mt-0.5">{{ __('ترخيص دائم (بدون تاريخ انتهاء).') }}</p>
            @endif
            @if ($status['plan'])
                <p class="text-xs text-ink-400 mt-0.5">{{ __('الباقة:') }} {{ $status['plan'] }}</p>
            @endif
        </div>
    </div>

    <div class="pt-6 border-t border-ink-100">
        <h4 class="text-sm font-bold text-ink-800 mb-1">{{ __('رمز هذا الجهاز') }}</h4>
        <p class="text-xs text-ink-400 mb-3">{{ __('أرسل هذا الرمز إلى IAM Agency للحصول على ترخيص خاص بهذا الجهاز. الترخيص لا يعمل على جهاز آخر.') }}</p>
        <div class="flex items-center gap-2">
            <code class="ltr-nums flex-1 rounded-xl border border-ink-200 bg-ink-50 px-3 py-2.5 font-mono text-sm tracking-widest text-ink-800" dir="ltr">{{ $machineCode }}</code>
            <button type="button" class="btn-secondary shrink-0"
                x-data
                x-on:click="window.copyText({{ Js::from($machineCode) }}).then(ok => $store.toasts.push(ok ? {{ Js::from(__('تم نسخ رمز الجهاز')) }} : {{ Js::from(__('تعذّر النسخ — انسخ الرمز يدوياً')) }}, ok ? 'success' : 'error'))">
                <x-icon name="file-text" class="w-4 h-4" /> {{ __('نسخ') }}
            </button>
        </div>
    </div>

    <div class="pt-6 border-t border-ink-100">
        <h4 class="text-sm font-bold text-ink-800 mb-1">{{ __('تفعيل ترخيص') }}</h4>
        <p class="text-xs text-ink-400 mb-3">{{ __('الصق نص الترخيص الذي استلمته، ثم اضغط تفعيل.') }}</p>
        <form wire:submit="activate" class="space-y-3">
            <textarea wire:model="licence" rows="4" dir="ltr"
                class="input ps-3 h-auto py-2 font-mono text-xs"
                placeholder="eyJwIjoi..."></textarea>
            @error('licence') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="activate">
                <x-icon name="check" class="w-4 h-4" /> {{ __('تفعيل الترخيص') }}
            </button>
        </form>
    </div>
</div>
