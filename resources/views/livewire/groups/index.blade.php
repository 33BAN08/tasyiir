@php
    $statusTone = ['نشط' => 'success', 'جديد' => 'info', 'متوقف مؤقتاً' => 'neutral'];
@endphp
<div>
    <x-page-header title="{{ __('المجموعات') }}" subtitle="{{ __('إدارة مجموعات وأفواج الدراسة') }}">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" /> {{ __('إنشاء مجموعة') }}
        </button>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="users-round" label="{{ __('إجمالي المجموعات') }}" :value="$stats['total']" tone="brand" />
        <x-stat-card icon="circle-check" label="{{ __('مجموعات نشطة') }}" :value="$stats['active']" tone="blue" />
        <x-stat-card icon="users" label="{{ __('متوسط الطلاب / مجموعة') }}" :value="$stats['avg_students']" tone="violet" />
        <x-stat-card icon="building-2" label="{{ __('القاعات المستعملة') }}" :value="$stats['rooms']" tone="amber" />
    </div>

    <x-filter-bar>
        <div class="relative flex-1">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.400ms="search" class="input" placeholder="{{ __('البحث باسم المجموعة أو الدورة...') }}" />
        </div>
        <select class="select sm:w-44" wire:model.live="courseFilter">
            <option value="">{{ __('كل الدورات') }}</option>
            @foreach ($courses as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select class="select sm:w-40" wire:model.live="statusFilter">
            <option value="">{{ __('كل الحالات') }}</option>
            @foreach ($statuses as $st)
                <option value="{{ $st }}">{{ __($st) }}</option>
            @endforeach
        </select>
    </x-filter-bar>

    <div class="relative">
        <div wire:loading.delay class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center rounded-2xl">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($groups as $g)
                @php $fill = $g->capacity > 0 ? min(100, round($g->students_count / $g->capacity * 100)) : 0; @endphp
                <div class="card p-5 flex flex-col gap-3.5" wire:key="group-{{ $g->id }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-ink-900">{{ $g->name }}</h3>
                            <p class="text-xs text-ink-400 mt-0.5">{{ $g->course?->name ?? __('بدون دورة') }}</p>
                        </div>
                        <x-status-badge :label="$g->status" :tone="$statusTone[$g->status] ?? 'neutral'" />
                    </div>

                    <div class="flex items-center gap-2.5 text-sm text-ink-600">
                        <x-avatar :name="$g->teacher?->name ?? __('؟')" size="sm" />
                        <span class="truncate">{{ $g->teacher?->name ?? __('بدون أستاذ') }}</span>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-ink-400">{{ __('عدد الطلاب') }}</span>
                            <span class="ltr-nums font-semibold text-ink-700">{{ $g->students_count }} / {{ $g->capacity }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-ink-100 overflow-hidden">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ $fill }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-ink-500">
                        <x-icon name="map-pin" class="w-3.5 h-3.5 text-ink-400" /> {{ $g->room ?? '—' }}
                    </div>
                    <div class="flex items-center gap-2 text-xs text-ink-500">
                        <x-icon name="clock" class="w-3.5 h-3.5 text-ink-400" /> {{ $g->schedule ?? '—' }}
                    </div>

                    <div class="flex items-center gap-2 pt-1 border-t border-ink-100 mt-1">
                        <button type="button" class="btn-secondary flex-1" wire:click="openEdit({{ $g->id }})"><x-icon name="pencil" class="w-4 h-4" /> {{ __('تعديل') }}</button>
                        <a href="/attendance?group={{ $g->id }}" class="btn-icon" aria-label="{{ __('الحضور') }}"><x-icon name="calendar-check" class="w-4 h-4" /></a>
                        <button type="button" class="btn-icon" wire:click="confirmDelete({{ $g->id }})" aria-label="{{ __('حذف') }}"><x-icon name="trash-2" class="w-4 h-4" /></button>
                    </div>
                </div>
            @empty
                <div class="card sm:col-span-2 lg:col-span-3">
                    <x-empty-state icon="users-round" title="{{ __('لا توجد مجموعات') }}" description="{{ __('لم يتم العثور على أي مجموعة مطابقة لبحثك أو الفلاتر المحددة.') }}" />
                </div>
            @endforelse
        </div>

        @if ($groups->total() > 0)
            <div class="card mt-4">
                <x-livewire-pagination :paginator="$groups" />
            </div>
        @endif
    </div>

    <!-- Add / Edit modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? __('تعديل بيانات المجموعة') : __('إنشاء مجموعة جديدة') }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="{{ __('إغلاق') }}">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('اسم المجموعة') }}</label>
                        <input type="text" wire:model="name" class="input ps-3" placeholder="{{ __('مثال: English A1 - A') }}" />
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('الدورة') }}</label>
                            <select class="select" wire:model="course_id">
                                <option value="">{{ __('بدون دورة') }}</option>
                                @foreach ($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('course_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('الأستاذ') }}</label>
                            <select class="select" wire:model="teacher_id">
                                <option value="">{{ __('بدون أستاذ') }}</option>
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('السعة') }}</label>
                            <input type="number" min="1" wire:model="capacity" class="input ps-3 ltr-nums" />
                            @error('capacity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('القاعة') }}</label>
                            <input type="text" wire:model="room" class="input ps-3" placeholder="{{ __('مثال: القاعة 1') }}" />
                            @error('room') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('التوقيت') }}</label>
                        <input type="text" wire:model="schedule" class="input ps-3" placeholder="{{ __('مثال: الاثنين والأربعاء 16:00 - 17:30') }}" />
                        @error('schedule') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('الحالة') }}</label>
                        <select class="select" wire:model="status">
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}">{{ __($st) }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">{{ __('إلغاء') }}</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="plus" class="w-4 h-4" />
                            {{ $editingId ? __('حفظ التعديلات') : __('إنشاء المجموعة') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete confirmation -->
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="cancelDelete"></div>
            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-popover overflow-hidden p-5 text-center">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-50 text-red-600 mx-auto mb-3">
                    <x-icon name="trash-2" class="w-5 h-5" />
                </span>
                <h2 class="text-base font-bold text-ink-900 mb-1.5">{{ __('حذف المجموعة') }}</h2>
                <p class="text-sm text-ink-500 mb-5">{{ __('هل أنت متأكد من حذف هذه المجموعة؟ يمكن استرجاعها لاحقاً من قبل المسؤول.') }}</p>
                <div class="flex items-center justify-center gap-2">
                    <button type="button" class="btn-secondary" wire:click="cancelDelete">{{ __('إلغاء') }}</button>
                    <button type="button" class="btn-danger" wire:click="delete">
                        <x-icon name="trash-2" class="w-4 h-4" /> {{ __('حذف نهائي') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
