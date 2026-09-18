@php
    $palette = ['bg-brand-50 text-brand-700 border-brand-200', 'bg-blue-50 text-blue-700 border-blue-200', 'bg-violet-50 text-violet-700 border-violet-200', 'bg-amber-50 text-amber-700 border-amber-200', 'bg-rose-50 text-rose-700 border-rose-200'];
@endphp
<div>
    <x-page-header title="الجدول الأسبوعي" subtitle="توقيت الحصص لجميع المجموعات على مدار الأسبوع">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" /> إضافة حصة
        </button>
    </x-page-header>

    <div class="card p-4 mb-6 flex flex-wrap items-center gap-3">
        <select class="select sm:w-48" wire:model.live="teacherFilter">
            <option value="">كل الأساتذة</option>
            @foreach ($teachers as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>
        <select class="select sm:w-40" wire:model.live="roomFilter">
            <option value="">كل القاعات</option>
            @foreach ($rooms as $r)
                <option value="{{ $r }}">{{ $r }}</option>
            @endforeach
        </select>
        <div class="flex items-center gap-3 text-xs text-ink-400 ms-auto">
            <span class="ltr-nums">{{ $totalSlots }} حصة</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-400"></span> نشط</span>
        </div>
    </div>

    <div class="overflow-x-auto pb-2 -mx-1 px-1 relative">
        <div wire:loading.delay wire:target="teacherFilter,roomFilter" class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center rounded-2xl">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>
        <div class="flex gap-4 min-w-max">
            @foreach ($days as $day)
                <div class="w-64 shrink-0">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h3 class="font-bold text-ink-800">{{ $day }}</h3>
                        <div class="flex items-center gap-1.5">
                            <span class="ltr-nums text-xs text-ink-400">{{ count($week[$day] ?? []) }} حصص</span>
                            <button type="button" class="btn-icon !p-1" wire:click="openCreate('{{ $day }}')" aria-label="إضافة حصة يوم {{ $day }}" title="إضافة حصة"><x-icon name="plus" class="w-3.5 h-3.5" /></button>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse ($week[$day] ?? [] as $i => $class)
                            @php $tone = $palette[$i % count($palette)]; @endphp
                            @php $conflict = $conflicts[$class->id] ?? null; @endphp
                            <div class="group relative rounded-xl border p-3.5 {{ $conflict ? 'ring-2 ring-red-400 ' : '' }}{{ $tone }}" wire:key="slot-{{ $class->id }}">
                                @if ($conflict)
                                    <span class="absolute top-2 start-2 inline-flex items-center gap-1 rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold text-white" title="{{ $conflict === 'room' ? 'القاعة محجوزة في نفس الوقت' : ($conflict === 'teacher' ? 'الأستاذ لديه حصة أخرى في نفس الوقت' : 'تعارض في القاعة والأستاذ') }}">
                                        <x-icon name="triangle-alert" class="w-3 h-3" /> تعارض {{ $conflict === 'room' ? 'قاعة' : ($conflict === 'teacher' ? 'أستاذ' : 'قاعة وأستاذ') }}
                                    </span>
                                @endif
                                <div class="absolute top-2 end-2 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                                    <button type="button" class="rounded-md p-1 text-ink-500 hover:bg-white/80 hover:text-ink-800" wire:click="openEdit({{ $class->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-3.5 h-3.5" /></button>
                                    <button type="button" class="rounded-md p-1 text-ink-500 hover:bg-white/80 hover:text-red-600" wire:click="confirmDelete({{ $class->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-3.5 h-3.5" /></button>
                                </div>
                                <p class="ltr-nums text-xs font-bold mb-1.5">{{ $class->time }}</p>
                                <p class="text-sm font-bold text-ink-900">{{ $class->course?->name ?? '—' }}</p>
                                <p class="text-xs text-ink-500 mt-0.5">{{ $class->group?->name ?? 'بدون مجموعة' }}</p>
                                <div class="flex items-center gap-1.5 mt-2 text-xs text-ink-600">
                                    <x-icon name="graduation-cap" class="w-3.5 h-3.5" /> {{ $class->teacher?->name ?? 'بدون أستاذ' }}
                                </div>
                                <div class="flex items-center gap-1.5 mt-1 text-xs text-ink-600">
                                    <x-icon name="map-pin" class="w-3.5 h-3.5" /> {{ $class->room ?? '—' }}
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-ink-200 p-4 text-center">
                                <p class="text-xs text-ink-400">لا توجد حصص</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Add / Edit modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('select')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? 'تعديل الحصة' : 'إضافة حصة جديدة' }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">اليوم</label>
                            <select class="select" wire:model="day">
                                @foreach ($days as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                            @error('day') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">التوقيت</label>
                            <input type="text" wire:model="time" class="input ps-3 ltr-nums" placeholder="14:00 - 15:30" dir="ltr" />
                            @error('time') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">الدورة</label>
                            <select class="select" wire:model.live="course_id">
                                <option value="">اختر دورة</option>
                                @foreach ($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('course_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">المجموعة</label>
                            <select class="select" wire:model.live="group_id" @if (! $course_id) disabled @endif>
                                <option value="">{{ $course_id ? 'بدون مجموعة' : 'اختر الدورة أولاً' }}</option>
                                @foreach ($groups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            @error('group_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">الأستاذ</label>
                            <select class="select" wire:model="teacher_id">
                                <option value="">بدون أستاذ</option>
                                @foreach ($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">القاعة</label>
                            <input type="text" wire:model="room" class="input ps-3" placeholder="مثال: القاعة 1" list="schedule-rooms" />
                            <datalist id="schedule-rooms">
                                @foreach ($rooms as $r)
                                    <option value="{{ $r }}"></option>
                                @endforeach
                            </datalist>
                            @error('room') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="plus" class="w-4 h-4" />
                            {{ $editingId ? 'حفظ التعديلات' : 'إضافة الحصة' }}
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
                <h2 class="text-base font-bold text-ink-900 mb-1.5">حذف الحصة</h2>
                <p class="text-sm text-ink-500 mb-5">هل أنت متأكد من حذف هذه الحصة من الجدول؟</p>
                <div class="flex items-center justify-center gap-2">
                    <button type="button" class="btn-secondary" wire:click="cancelDelete">إلغاء</button>
                    <button type="button" class="btn-danger" wire:click="delete">
                        <x-icon name="trash-2" class="w-4 h-4" /> حذف
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
