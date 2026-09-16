@extends('layouts.app')

@section('title', 'الإشعارات')

@section('content')
@php
    $notifications = \App\Support\Mock\Notifications::all();
    $unread = \App\Support\Mock\Notifications::unreadCount();
    $toneClasses = [
        'brand' => 'bg-brand-50 text-brand-600', 'blue' => 'bg-blue-50 text-blue-600', 'amber' => 'bg-amber-50 text-amber-600',
        'violet' => 'bg-violet-50 text-violet-600', 'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp

<x-page-header title="الإشعارات" subtitle="جميع تنبيهات وأحداث المركز">
    <button type="button" class="btn-secondary" data-toast="تم تعليم الكل كمقروء (تجريبي)">
        <x-icon name="check-check" class="w-4 h-4" /> تعليم الكل كمقروء
    </button>
</x-page-header>

<div
    x-data="{
        filter: 'all',
        items: {{ json_encode($notifications, JSON_UNESCAPED_UNICODE) }},
        get filtered() { return this.filter === 'all' ? this.items : this.items.filter(n => !n.read); },
    }"
>
    <div class="card p-1.5 flex items-center gap-1 mb-5 w-fit">
        <button type="button" x-on:click="filter = 'all'" :class="filter === 'all' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors">الكل</button>
        <button type="button" x-on:click="filter = 'unread'" :class="filter === 'unread' ? 'bg-brand-600 text-white' : 'text-ink-500 hover:bg-ink-100'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
            غير المقروءة
            <span class="ltr-nums inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-red-500 text-white text-[11px] ms-1">{{ $unread }}</span>
        </button>
    </div>

    <div class="card divide-y divide-ink-100 overflow-hidden">
        <template x-for="n in filtered" :key="n.id">
            <div class="flex items-start gap-4 p-4 sm:p-5" :class="!n.read ? 'bg-brand-50/30' : ''">
                <span
                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl shrink-0"
                    :class="{
                        'bg-brand-50 text-brand-600': n.tone === 'brand',
                        'bg-blue-50 text-blue-600': n.tone === 'blue',
                        'bg-amber-50 text-amber-600': n.tone === 'amber',
                        'bg-violet-50 text-violet-600': n.tone === 'violet',
                        'bg-rose-50 text-rose-600': n.tone === 'rose',
                    }"
                    x-html="document.getElementById('icon-tpl-' + n.icon)?.innerHTML ?? ''"
                ></span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-ink-800" x-text="n.title"></p>
                        <span x-show="!n.read" class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                    </div>
                    <p class="text-sm text-ink-500 mt-0.5" x-text="n.body"></p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-xs text-ink-400" x-text="n.time"></span>
                        <span class="text-ink-300">·</span>
                        <span class="text-xs text-ink-400" x-text="n.category"></span>
                    </div>
                </div>
                <button type="button" class="btn-icon shrink-0" x-on:click="n.read = true" aria-label="تعليم كمقروء" x-show="!n.read">
                    <x-icon name="check" class="w-4 h-4" />
                </button>
            </div>
        </template>

        <div x-show="filtered.length === 0" class="p-0">
            <x-empty-state icon="bell" title="لا توجد إشعارات" description="لا توجد إشعارات غير مقروءة حالياً." />
        </div>
    </div>
</div>

<!-- Hidden icon templates for the Alpine x-html lookups above -->
<div class="hidden">
    @foreach (['user-round-plus', 'wallet', 'triangle-alert', 'calendar-check', 'clipboard-list', 'calendar-days', 'banknote', 'receipt', 'users-round', 'bell'] as $iconName)
        <div id="icon-tpl-{{ $iconName }}"><x-icon :name="$iconName" class="w-5 h-5" /></div>
    @endforeach
</div>
@endsection
