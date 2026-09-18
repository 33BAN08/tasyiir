@php
    $statusTone = ['نشط' => 'success', 'في إجازة' => 'warning', 'متوقف' => 'neutral'];
    $salaryLabel = fn ($t) => $t->isCommissionBased()
        ? number_format((int) $t->commission_rate).'% عمولة'
        : mad((int) $t->fixed_salary).'/شهر';
@endphp
<div>
    <x-page-header title="الأساتذة" subtitle="إدارة فريق التدريس بالمركز">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" /> إضافة أستاذ
        </button>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="graduation-cap" label="إجمالي الأساتذة" :value="$stats['total']" tone="brand" />
        <x-stat-card icon="circle-check" label="الأساتذة النشطون" :value="$stats['active']" tone="blue" />
        <x-stat-card icon="calendar-days" label="في إجازة" :value="$stats['on_leave']" tone="amber" />
        <x-stat-card icon="clock" label="إجمالي ساعات التدريس" :value="$stats['total_hours']" tone="violet" />
    </div>

    <x-filter-bar>
        <div class="relative flex-1">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.400ms="search" class="input" placeholder="البحث باسم الأستاذ أو التخصص..." />
        </div>
        <select class="select sm:w-48" wire:model.live="specialtyFilter">
            <option value="">كل التخصصات</option>
            @foreach ($specialties as $sp)
                <option value="{{ $sp }}">{{ $sp }}</option>
            @endforeach
        </select>
        <select class="select sm:w-40" wire:model.live="statusFilter">
            <option value="">كل الحالات</option>
            @foreach ($statuses as $st)
                <option value="{{ $st }}">{{ $st }}</option>
            @endforeach
        </select>
    </x-filter-bar>

    <div class="relative">
        <div wire:loading.delay class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center rounded-2xl">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>

        <!-- Desktop table -->
        <div class="card overflow-hidden hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-ink-50 border-b border-ink-100">
                        <tr>
                            <th class="table-head-cell">الأستاذ</th>
                            <th class="table-head-cell">التخصص</th>
                            <th class="table-head-cell">المجموعات</th>
                            <th class="table-head-cell">الطلاب</th>
                            <th class="table-head-cell">ساعات التدريس</th>
                            <th class="table-head-cell">الأجر</th>
                            <th class="table-head-cell">الحالة</th>
                            <th class="table-head-cell"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($teachers as $t)
                            <tr class="hover:bg-ink-50/70 transition-colors">
                                <td class="table-cell">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :name="$t->name" size="sm" />
                                        <div>
                                            <p class="font-semibold text-ink-800">{{ $t->name }}</p>
                                            <p class="text-xs text-ink-400 ltr-nums">{{ $t->phone ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">{{ $t->specialty ?? '—' }}</td>
                                <td class="table-cell ltr-nums">{{ $t->groups_count }}</td>
                                <td class="table-cell ltr-nums">{{ $t->students_count }}</td>
                                <td class="table-cell ltr-nums">{{ $t->hours }} س/أسبوع</td>
                                <td class="table-cell">
                                    <span class="ltr-nums">{{ $salaryLabel($t) }}</span>
                                    <span class="block text-[11px] text-ink-400">{{ $t->salary_type }}</span>
                                </td>
                                <td class="table-cell"><x-status-badge :label="$t->status" :tone="$statusTone[$t->status] ?? 'neutral'" /></td>
                                <td class="table-cell">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" class="btn-icon" wire:click="openEdit({{ $t->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                                        <button type="button" class="btn-icon" wire:click="confirmDelete({{ $t->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state icon="graduation-cap" title="لا يوجد أساتذة" description="لم يتم العثور على أي أستاذ مطابق لبحثك أو الفلاتر المحددة." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($teachers->total() > 0)
                <x-livewire-pagination :paginator="$teachers" />
            @endif
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse ($teachers as $t)
                    <div class="card p-4 flex items-center gap-3">
                        <x-avatar :name="$t->name" size="md" />
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-ink-800 truncate">{{ $t->name }}</p>
                            <p class="text-xs text-ink-400 truncate">{{ $t->specialty ?? '—' }}</p>
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <x-status-badge :label="$t->status" :tone="$statusTone[$t->status] ?? 'neutral'" />
                                <span class="text-xs text-ink-400 ltr-nums">{{ $t->groups_count }} مجموعات</span>
                            </div>
                            <p class="text-xs text-ink-500 mt-1.5 ltr-nums">{{ $salaryLabel($t) }}</p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" class="btn-icon" wire:click="openEdit({{ $t->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                            <button type="button" class="btn-icon" wire:click="confirmDelete({{ $t->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                        </div>
                    </div>
                @empty
                    <div class="card sm:col-span-2">
                        <x-empty-state icon="graduation-cap" title="لا يوجد أساتذة" description="لم يتم العثور على أي أستاذ مطابق لبحثك أو الفلاتر المحددة." />
                    </div>
                @endforelse
            </div>
            @if ($teachers->total() > 0)
                <div class="card mt-4">
                    <x-livewire-pagination :paginator="$teachers" />
                </div>
            @endif
        </div>
    </div>

    <!-- Add / Edit modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? 'تعديل بيانات الأستاذ' : 'إضافة أستاذ جديد' }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">اسم الأستاذ</label>
                        <input type="text" wire:model="name" class="input ps-3" placeholder="مثال: أستاذ يوسف الفاسي" />
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">التخصص</label>
                            <input type="text" wire:model="specialty" class="input ps-3" placeholder="مثال: اللغة الإنجليزية" list="teacher-specialties" />
                            <datalist id="teacher-specialties">
                                @foreach ($specialties as $sp)
                                    <option value="{{ $sp }}"></option>
                                @endforeach
                            </datalist>
                            @error('specialty') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">ساعات التدريس / أسبوع</label>
                            <input type="number" min="0" wire:model="hours" class="input ps-3 ltr-nums" />
                            @error('hours') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">رقم الهاتف</label>
                        <input type="text" wire:model="phone" class="input ps-3" placeholder="06XX-XX-XX-XX" />
                        @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">نوع الأجر</label>
                            <select class="select" wire:model.live="salary_type">
                                @foreach ($salaryTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('salary_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        @if ($salary_type === 'بالعمولة')
                            <div wire:key="salary-commission">
                                <label class="block text-sm font-medium text-ink-700 mb-1.5">نسبة العمولة (%)</label>
                                <input type="number" min="0" max="100" wire:model="commission_rate" class="input ps-3 ltr-nums" placeholder="مثال: 40" />
                                @error('commission_rate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @else
                            <div wire:key="salary-fixed">
                                <label class="block text-sm font-medium text-ink-700 mb-1.5">الراتب الثابت (MAD)</label>
                                <input type="number" min="0" wire:model="fixed_salary" class="input ps-3 ltr-nums" placeholder="مثال: 4000" />
                                @error('fixed_salary') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">الحالة</label>
                        <select class="select" wire:model="status">
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="plus" class="w-4 h-4" />
                            {{ $editingId ? 'حفظ التعديلات' : 'إضافة الأستاذ' }}
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
                <h2 class="text-base font-bold text-ink-900 mb-1.5">حذف الأستاذ</h2>
                <p class="text-sm text-ink-500 mb-5">هل أنت متأكد من حذف هذا الأستاذ؟ ستُفصل دوراته ومجموعاته عنه ويمكن استرجاعه لاحقاً من قبل المسؤول.</p>
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
