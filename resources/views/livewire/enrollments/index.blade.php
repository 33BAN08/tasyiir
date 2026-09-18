@php
    $statusTone = ['مكتمل' => 'success', 'جزئي' => 'warning', 'غير مؤدي' => 'danger'];
@endphp
<div>
    <x-page-header title="التسجيلات" subtitle="جميع تسجيلات الطلاب في الدورات والمجموعات">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" /> تسجيل جديد
        </button>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="clipboard-list" label="إجمالي التسجيلات" :value="$stats['total']" tone="brand" />
        <x-stat-card icon="triangle-alert" label="متأخرون عن الأداء" :value="$stats['overdue']" tone="rose" />
        <x-stat-card icon="banknote" label="القيمة الإجمالية" :value="mad($stats['total_value'])" tone="violet" />
        <x-stat-card icon="wallet" label="المبالغ المتبقية" :value="mad($stats['remaining'])" tone="amber" />
    </div>

    <x-filter-bar>
        <div class="relative flex-1">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.400ms="search" class="input" placeholder="البحث باسم الطالب..." />
        </div>
        <select class="select sm:w-44" wire:model.live="courseFilter">
            <option value="">كل الدورات</option>
            @foreach ($courses as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select class="select sm:w-36" wire:model.live="statusFilter">
            <option value="">كل الحالات</option>
            @foreach ($statuses as $st)
                <option value="{{ $st }}">{{ $st }}</option>
            @endforeach
            <option value="overdue">متأخرون عن الأداء</option>
        </select>
    </x-filter-bar>

    <div class="card overflow-hidden relative">
        <div wire:loading.delay class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">الطالب</th>
                        <th class="table-head-cell">الدورة</th>
                        <th class="table-head-cell">المجموعة</th>
                        <th class="table-head-cell">تاريخ التسجيل</th>
                        <th class="table-head-cell">الاستحقاق</th>
                        <th class="table-head-cell">السعر</th>
                        <th class="table-head-cell">الخصم</th>
                        <th class="table-head-cell">المتبقي</th>
                        <th class="table-head-cell">الحالة</th>
                        <th class="table-head-cell"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($enrollments as $r)
                        <tr class="hover:bg-ink-50/70 transition-colors">
                            <td class="table-cell">
                                @if ($r->student)
                                    <a href="{{ route('students.show', $r->student) }}" class="flex items-center gap-3 group">
                                        <x-avatar :name="$r->student->name" size="sm" />
                                        <span class="font-semibold text-ink-800 group-hover:text-brand-700">{{ $r->student->name }}</span>
                                    </a>
                                @else
                                    <span class="text-ink-400">طالب محذوف</span>
                                @endif
                            </td>
                            <td class="table-cell">{{ $r->course?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $r->group?->name ?? '—' }}</td>
                            <td class="table-cell ltr-nums">{{ $r->date->format('Y-m-d') }}</td>
                            <td class="table-cell">
                                @if ($r->due_date)
                                    <span class="ltr-nums {{ $r->is_overdue ? 'text-red-600 font-semibold' : '' }}">{{ $r->due_date->format('Y-m-d') }}</span>
                                    @if ($r->is_overdue)
                                        <span class="block text-[11px] text-red-500">متأخر {{ $r->due_date->diffInDays(today()) }} يوم</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="table-cell ltr-nums">{{ mad($r->price) }}</td>
                            <td class="table-cell ltr-nums">{{ $r->discount > 0 ? mad($r->discount) : '—' }}</td>
                            <td class="table-cell ltr-nums">{{ mad($r->remaining) }}</td>
                            <td class="table-cell"><x-status-badge :label="$r->status" :tone="$statusTone[$r->status] ?? 'neutral'" /></td>
                            <td class="table-cell">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-icon" wire:click="openEdit({{ $r->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                                    <button type="button" class="btn-icon" wire:click="confirmDelete({{ $r->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <x-empty-state icon="clipboard-list" title="لا توجد تسجيلات" description="لم يتم العثور على أي تسجيل مطابق لبحثك أو الفلاتر المحددة." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-ink-100">
            @forelse ($enrollments as $r)
                <div class="p-4">
                    <div class="flex items-center gap-3 mb-2">
                        <x-avatar :name="$r->student?->name ?? '؟'" size="sm" />
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-ink-800 truncate">{{ $r->student?->name ?? 'طالب محذوف' }}</p>
                            <p class="text-xs text-ink-400 truncate">{{ $r->course?->name ?? '—' }} · {{ $r->group?->name ?? '—' }}</p>
                        </div>
                        <x-status-badge :label="$r->status" :tone="$statusTone[$r->status] ?? 'neutral'" />
                    </div>
                    <div class="flex items-center justify-between text-xs text-ink-500 ltr-nums">
                        <span>{{ $r->date->format('Y-m-d') }}</span>
                        <span>السعر: {{ mad($r->price) }}</span>
                        <span>المتبقي: {{ mad($r->remaining) }}</span>
                    </div>
                    @if ($r->due_date)
                        <p class="text-xs mt-1 {{ $r->is_overdue ? 'text-red-600 font-semibold' : 'text-ink-400' }}">
                            الاستحقاق: <span class="ltr-nums">{{ $r->due_date->format('Y-m-d') }}</span>{{ $r->is_overdue ? ' — متأخر '.$r->due_date->diffInDays(today()).' يوم' : '' }}
                        </p>
                    @endif
                    <div class="flex items-center justify-end gap-1 mt-2">
                        <button type="button" class="btn-icon" wire:click="openEdit({{ $r->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                        <button type="button" class="btn-icon" wire:click="confirmDelete({{ $r->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                    </div>
                </div>
            @empty
                <x-empty-state icon="clipboard-list" title="لا توجد تسجيلات" description="لم يتم العثور على أي تسجيل مطابق لبحثك أو الفلاتر المحددة." />
            @endforelse
        </div>

        @if ($enrollments->total() > 0)
            <x-livewire-pagination :paginator="$enrollments" />
        @endif
    </div>

    <!-- Add / Edit modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('select')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? 'تعديل التسجيل' : 'تسجيل جديد' }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
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
                                    <option value="{{ $g->id }}">{{ $g->name }}{{ $g->teacher ? ' — '.$g->teacher->name : '' }}</option>
                                @endforeach
                            </select>
                            @error('group_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($selectedCourse)
                        @php $teacher = $selectedGroup?->teacher ?? $selectedCourse->teacher; @endphp
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 rounded-xl bg-ink-50 px-4 py-2.5 text-xs text-ink-600">
                            <span class="flex items-center gap-1.5"><x-icon name="graduation-cap" class="w-3.5 h-3.5 text-ink-400" /> {{ $teacher?->name ?? 'بدون أستاذ' }}</span>
                            @if ($selectedGroup?->room)
                                <span class="flex items-center gap-1.5"><x-icon name="map-pin" class="w-3.5 h-3.5 text-ink-400" /> {{ $selectedGroup->room }}</span>
                            @endif
                            @if ($selectedGroup?->schedule)
                                <span class="flex items-center gap-1.5"><x-icon name="clock" class="w-3.5 h-3.5 text-ink-400" /> {{ $selectedGroup->schedule }}</span>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">الطالب</label>
                        @if ($pinnedStudent && ! $editingId)
                            <div class="flex items-center gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-2.5">
                                <x-avatar :name="$pinnedStudent->name" size="sm" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-ink-800 truncate">{{ $pinnedStudent->name }}</p>
                                    <p class="text-xs text-ink-500 ltr-nums">{{ $pinnedStudent->phone }}</p>
                                </div>
                                <button type="button" class="text-xs font-semibold text-brand-700 hover:text-brand-800" wire:click="unpinStudent">تغيير</button>
                            </div>
                        @elseif ($editingId)
                            <select class="select" wire:model="student_id" disabled>
                                @foreach ($students as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        @elseif (! $course_id)
                            <select class="select" disabled>
                                <option>اختر الدورة أولاً</option>
                            </select>
                        @else
                            <div class="relative mb-2">
                                <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                                    <x-icon name="search" class="w-4 h-4" />
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="studentSearch" class="input" placeholder="ابحث باسم الطالب لتصفية القائمة..." />
                            </div>
                            <select class="select" wire:model="student_id" size="5">
                                @forelse ($students as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->phone }}</option>
                                @empty
                                    <option value="" disabled>{{ $studentSearch ? 'لا يوجد طالب مطابق' : 'كل الطلاب مسجلون في هذه الدورة' }}</option>
                                @endforelse
                            </select>
                            <p class="text-[11px] text-ink-400 mt-1">{{ $students->count() }} طالب متاح للتسجيل في هذه الدورة</p>
                        @endif
                        @error('student_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">تاريخ التسجيل</label>
                            <input type="date" wire:model.live="date" class="input ps-3 ltr-nums" />
                            @error('date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">تاريخ الاستحقاق</label>
                            <input type="date" wire:model="due_date" class="input ps-3 ltr-nums" />
                            <p class="text-[11px] text-ink-400 mt-1">بعده يُعتبر الطالب غير مؤدٍ للشهر الجديد</p>
                            @error('due_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">السعر</label>
                            <input type="number" min="0" wire:model.live.debounce.300ms="price" class="input ps-3 ltr-nums" />
                            @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">الخصم</label>
                            <input type="number" min="0" wire:model.live.debounce.300ms="discount" class="input ps-3 ltr-nums" />
                            @error('discount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">المؤدى</label>
                            <input type="number" min="0" wire:model.live.debounce.300ms="paid" class="input ps-3 ltr-nums" />
                            @error('paid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-ink-50 px-4 py-3 text-sm">
                        <span class="text-ink-500">المتبقي: <span class="ltr-nums font-bold {{ $preview['remaining'] > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ mad($preview['remaining']) }}</span></span>
                        <x-status-badge :label="$preview['status']" :tone="$statusTone[$preview['status']] ?? 'neutral'" />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="plus" class="w-4 h-4" />
                            {{ $editingId ? 'حفظ التعديلات' : 'تسجيل الطالب' }}
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
                <h2 class="text-base font-bold text-ink-900 mb-1.5">حذف التسجيل</h2>
                <p class="text-sm text-ink-500 mb-5">هل أنت متأكد من حذف هذا التسجيل؟ يمكن استرجاعه لاحقاً من قبل المسؤول.</p>
                <div class="flex items-center justify-center gap-2">
                    <button type="button" class="btn-secondary" wire:click="cancelDelete">إلغاء</button>
                    <button type="button" class="btn-danger" wire:click="delete">
                        <x-icon name="trash-2" class="w-4 h-4" /> حذف نهائي
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
