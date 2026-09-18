@php
    $toneClasses = [
        'brand' => 'bg-brand-50 text-brand-600', 'blue' => 'bg-blue-50 text-blue-600', 'amber' => 'bg-amber-50 text-amber-600',
        'violet' => 'bg-violet-50 text-violet-600', 'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp
<div>
    <x-page-header title="الإشعارات" subtitle="جميع تنبيهات وأحداث المركز">
        <button type="button" class="btn-secondary" wire:click="markAllRead" @if ($unread === 0) disabled @endif>
            <x-icon name="check-check" class="w-4 h-4" /> تعليم الكل كمقروء
        </button>
    </x-page-header>

    <div class="card p-1.5 flex items-center gap-1 mb-5 w-fit">
        <button type="button" wire:click="$set('unreadOnly', false)" class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ ! $unreadOnly ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100' }}">الكل</button>
        <button type="button" wire:click="$set('unreadOnly', true)" class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ $unreadOnly ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100' }}">
            غير المقروءة
            @if ($unread > 0)
                <span class="ltr-nums inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-red-500 text-white text-[11px] ms-1">{{ $unread }}</span>
            @endif
        </button>
    </div>

    <div class="card divide-y divide-ink-100 overflow-hidden relative">
        <div wire:loading.delay class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>

        @forelse ($notifications as $n)
            <div class="flex items-start gap-4 p-4 sm:p-5 {{ $n->read ? '' : 'bg-brand-50/30' }}" wire:key="notif-{{ $n->id }}">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl shrink-0 {{ $toneClasses[$n->tone] ?? $toneClasses['brand'] }}">
                    <x-icon :name="$n->icon ?? 'bell'" class="w-5 h-5" />
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-ink-800">{{ $n->title }}</p>
                        @unless ($n->read)
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        @endunless
                    </div>
                    @if ($n->body)
                        <p class="text-sm text-ink-500 mt-0.5">{{ $n->body }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-xs text-ink-400" title="{{ $n->created_at->format('Y-m-d H:i') }}">{{ $n->created_at->diffForHumans() }}</span>
                        @if ($n->category)
                            <span class="text-ink-300">·</span>
                            <span class="text-xs text-ink-400">{{ $n->category }}</span>
                        @endif
                    </div>
                </div>
                @unless ($n->read)
                    <button type="button" class="btn-icon shrink-0" wire:click="markRead({{ $n->id }})" aria-label="تعليم كمقروء" title="تعليم كمقروء">
                        <x-icon name="check" class="w-4 h-4" />
                    </button>
                @endunless
            </div>
        @empty
            <x-empty-state icon="bell" title="لا توجد إشعارات" :description="$unreadOnly ? 'لا توجد إشعارات غير مقروءة حالياً.' : 'لم يُسجَّل أي حدث بعد.'" />
        @endforelse

        @if ($notifications->hasPages())
            <x-livewire-pagination :paginator="$notifications" />
        @endif
    </div>
</div>
