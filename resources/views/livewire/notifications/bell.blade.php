@php
    $toneClasses = [
        'brand' => 'bg-brand-50 text-brand-600', 'blue' => 'bg-blue-50 text-blue-600',
        'amber' => 'bg-amber-50 text-amber-600', 'violet' => 'bg-violet-50 text-violet-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp
<div class="relative" x-data="{ open: false }" wire:poll.30s>
    <button type="button" x-on:click="open = !open" class="btn-icon relative" aria-label="الإشعارات">
        <x-icon name="bell" class="w-5 h-5" />
        @if ($unread > 0)
            <span class="absolute top-1.5 end-1.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white"></span>
        @endif
    </button>
    <x-dropdown-panel align="end" width="w-80">
        <div class="flex items-center justify-between px-3.5 pb-2 mb-1 border-b border-ink-100">
            <p class="text-sm font-bold text-ink-800">الإشعارات</p>
            @if ($unread > 0)
                <span class="text-xs font-semibold text-brand-600 ltr-nums">{{ $unread }} جديدة</span>
            @else
                <span class="text-xs text-ink-400">لا جديد</span>
            @endif
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse ($items as $n)
                <div class="flex items-start gap-3 px-3.5 py-2.5 hover:bg-ink-50 {{ $n->read ? '' : 'bg-brand-50/30' }}" wire:key="bell-{{ $n->id }}">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg shrink-0 {{ $toneClasses[$n->tone] ?? $toneClasses['brand'] }}">
                        <x-icon :name="$n->icon ?? 'bell'" class="w-4 h-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-ink-700 leading-snug">{{ $n->title }}</p>
                        <p class="text-xs text-ink-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($n->read)
                        <button type="button" class="btn-icon !p-1 shrink-0" wire:click="markRead({{ $n->id }})" aria-label="تعليم كمقروء" title="تعليم كمقروء">
                            <x-icon name="check" class="w-3.5 h-3.5" />
                        </button>
                    @endunless
                </div>
            @empty
                <p class="px-3.5 py-6 text-center text-xs text-ink-400">لا توجد إشعارات بعد</p>
            @endforelse
        </div>
        <div class="px-3.5 pt-2 mt-1 border-t border-ink-100">
            <a href="{{ route('notifications.index') }}" class="block text-center text-sm font-semibold text-brand-600 hover:text-brand-700 py-1">عرض كل الإشعارات</a>
        </div>
    </x-dropdown-panel>
</div>
