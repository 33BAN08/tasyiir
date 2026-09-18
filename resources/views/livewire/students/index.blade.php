@php
    $financeTone = ['مؤدي' => 'success', 'جزئي' => 'warning', 'غير مؤدي' => 'danger'];
    $statusTone = ['نشط' => 'success', 'متوقف' => 'neutral'];
@endphp
<div>
    <x-page-header title="الطلاب" subtitle="إدارة جميع طلاب المركز">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" />
            إضافة طالب
        </button>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="users" label="إجمالي الطلاب" :value="$stats['total']" tone="brand" />
        <x-stat-card icon="circle-check" label="الطلاب النشطون" :value="$stats['active']" tone="blue" />
        <x-stat-card icon="circle-x" label="الطلاب المتوقفون" :value="$stats['paused']" tone="amber" />
        <x-stat-card icon="triangle-alert" label="الطلاب غير المؤدين" :value="$stats['unpaid']" tone="rose" />
    </div>

    <x-filter-bar>
        <div class="relative flex-1">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.400ms="search" class="input" placeholder="البحث باسم الطالب أو رقم الهاتف..." />
        </div>
        <select class="select sm:w-44" wire:model.live="course">
            <option value="">كل الدورات</option>
            @foreach ($courses as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select class="select sm:w-40" wire:model.live="status">
            <option value="">كل الحالات</option>
            <option value="نشط">نشط</option>
            <option value="متوقف">متوقف</option>
        </select>
        <select class="select sm:w-44" wire:model.live="financial">
            <option value="">الحالة المالية: الكل</option>
            <option value="مؤدي">مؤدي</option>
            <option value="جزئي">جزئي</option>
            <option value="غير مؤدي">غير مؤدي</option>
        </select>
    </x-filter-bar>

    @if ($enrollPrompt)
        <div class="card mb-4 p-4 flex flex-col sm:flex-row sm:items-center gap-3 border-brand-200 bg-brand-50">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-brand-100 text-brand-700 shrink-0">
                <x-icon name="clipboard-list" class="w-5 h-5" />
            </span>
            <p class="flex-1 text-sm text-ink-700">تمت إضافة <span class="font-semibold">{{ $enrollPrompt['name'] }}</span>. هل تريد تسجيله في دورة الآن؟</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('enrollments.index', ['student' => $enrollPrompt['id']]) }}" class="btn-primary">
                    <x-icon name="clipboard-list" class="w-4 h-4" /> تسجيل في دورة
                </a>
                <button type="button" class="btn-ghost" wire:click="dismissEnrollPrompt">لاحقاً</button>
            </div>
        </div>
    @endif

    <div class="card overflow-hidden relative">
        <div wire:loading.delay class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">الطالب</th>
                        <th class="table-head-cell">الهاتف</th>
                        <th class="table-head-cell">الدورة</th>
                        <th class="table-head-cell">المجموعة</th>
                        <th class="table-head-cell">تاريخ التسجيل</th>
                        <th class="table-head-cell">حالة التسجيل</th>
                        <th class="table-head-cell">الحالة المالية</th>
                        <th class="table-head-cell"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($students as $s)
                        <tr class="hover:bg-ink-50/70 transition-colors">
                            <td class="table-cell">
                                <a href="{{ route('students.show', $s) }}" class="flex items-center gap-3 group">
                                    <x-avatar :name="$s->name" size="sm" />
                                    <span class="font-semibold text-ink-800 group-hover:text-brand-700">{{ $s->name }}</span>
                                </a>
                            </td>
                            <td class="table-cell ltr-nums">{{ $s->phone }}</td>
                            <td class="table-cell">{{ $s->course?->name ?? '—' }}</td>
                            <td class="table-cell">{{ $s->group?->name ?? '—' }}</td>
                            <td class="table-cell ltr-nums">{{ $s->registered_at->format('Y-m-d') }}</td>
                            <td class="table-cell"><x-status-badge :label="$s->enrollment_status" :tone="$statusTone[$s->enrollment_status] ?? 'neutral'" /></td>
                            <td class="table-cell"><x-status-badge :label="$s->financial_status" :tone="$financeTone[$s->financial_status] ?? 'neutral'" /></td>
                            <td class="table-cell">
                                <div class="relative flex items-center justify-end gap-1">
                                    <a href="{{ route('students.show', $s) }}" class="btn-icon" aria-label="عرض"><x-icon name="eye" class="w-4 h-4" /></a>
                                    <a href="{{ route('enrollments.index', ['student' => $s->id]) }}" class="btn-icon" aria-label="تسجيل في دورة" title="تسجيل في دورة"><x-icon name="clipboard-list" class="w-4 h-4" /></a>
                                    <button type="button" class="btn-icon" wire:click="openEdit({{ $s->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                                    <button type="button" class="btn-icon" wire:click="confirmDelete({{ $s->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state icon="users" title="لا يوجد طلاب" description="لم يتم العثور على أي طالب مطابق لبحثك أو الفلاتر المحددة." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden divide-y divide-ink-100">
            @forelse ($students as $s)
                <div class="flex items-center gap-3 p-4">
                    <a href="{{ route('students.show', $s) }}" class="flex items-center gap-3 flex-1 min-w-0">
                        <x-avatar :name="$s->name" size="md" />
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-ink-800 truncate">{{ $s->name }}</p>
                            <p class="text-xs text-ink-400 truncate">{{ $s->course?->name ?? '—' }}</p>
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <x-status-badge :label="$s->enrollment_status" :tone="$statusTone[$s->enrollment_status] ?? 'neutral'" />
                                <x-status-badge :label="$s->financial_status" :tone="$financeTone[$s->financial_status] ?? 'neutral'" />
                            </div>
                        </div>
                    </a>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('enrollments.index', ['student' => $s->id]) }}" class="btn-icon" aria-label="تسجيل في دورة"><x-icon name="clipboard-list" class="w-4 h-4" /></a>
                        <button type="button" class="btn-icon" wire:click="openEdit({{ $s->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                        <button type="button" class="btn-icon" wire:click="confirmDelete({{ $s->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                    </div>
                </div>
            @empty
                <x-empty-state icon="users" title="لا يوجد طلاب" description="لم يتم العثور على أي طالب مطابق لبحثك أو الفلاتر المحددة." />
            @endforelse
        </div>

        @if ($students->total() > 0)
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3.5 border-t border-ink-100">
                <p class="text-sm text-ink-500 order-2 sm:order-1">
                    عرض <span class="ltr-nums font-semibold text-ink-700">{{ $students->firstItem() }}–{{ $students->lastItem() }}</span>
                    من <span class="ltr-nums font-semibold text-ink-700">{{ $students->total() }}</span> نتيجة
                </p>
                <nav class="flex items-center gap-1 order-1 sm:order-2" aria-label="ترقيم الصفحات">
                    <button type="button" class="btn-icon" wire:click="previousPage" @if ($students->onFirstPage()) disabled @endif aria-label="السابق">
                        <x-icon name="chevron-right" class="w-4 h-4" />
                    </button>
                    @for ($p = 1; $p <= $students->lastPage(); $p++)
                        <button type="button" wire:click="gotoPage({{ $p }})"
                            class="ltr-nums min-w-[2.25rem] h-9 rounded-lg text-sm font-semibold {{ $p === $students->currentPage() ? 'bg-brand-600 text-white' : 'text-ink-600 hover:bg-ink-100' }}">{{ $p }}</button>
                    @endfor
                    <button type="button" class="btn-icon" wire:click="nextPage" @if (!$students->hasMorePages()) disabled @endif aria-label="التالي">
                        <x-icon name="chevron-left" class="w-4 h-4" />
                    </button>
                </nav>
            </div>
        @endif
    </div>

    <!-- Add / Edit modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? 'تعديل بيانات الطالب' : 'إضافة طالب جديد' }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">الاسم الكامل</label>
                        <input type="text" wire:model="name" class="input ps-3" placeholder="مثال: يوسف العلوي" />
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">رقم الهاتف</label>
                            <input type="text" wire:model="phone" class="input ps-3" placeholder="06XX-XX-XX-XX" />
                            @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">المدينة</label>
                            <input type="text" wire:model="city" class="input ps-3" placeholder="المدينة" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">هاتف ولي الأمر</label>
                            <input type="text" wire:model="guardian_phone" class="input ps-3" placeholder="06XX-XX-XX-XX" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">الدورة</label>
                            <select class="select" wire:model="course_id">
                                <option value="">بدون دورة</option>
                                @foreach ($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">حالة التسجيل</label>
                            <select class="select" wire:model="enrollment_status">
                                <option value="نشط">نشط</option>
                                <option value="متوقف">متوقف</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">الحالة المالية</label>
                            <select class="select" wire:model="financial_status">
                                <option value="مؤدي">مؤدي</option>
                                <option value="جزئي">جزئي</option>
                                <option value="غير مؤدي">غير مؤدي</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="plus" class="w-4 h-4" />
                            {{ $editingId ? 'حفظ التعديلات' : 'إضافة الطالب' }}
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
                <h2 class="text-base font-bold text-ink-900 mb-1.5">حذف الطالب</h2>
                <p class="text-sm text-ink-500 mb-5">هل أنت متأكد من حذف هذا الطالب؟ يمكن استرجاعه لاحقاً من قبل المسؤول.</p>
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
