@php
    $statusTone = ['نشط' => 'success', 'جديد' => 'info', 'متوقف مؤقتاً' => 'neutral'];
@endphp
<div>
    <x-page-header title="الدورات" subtitle="إدارة الدورات التكوينية المقدمة بالمركز">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" /> إنشاء دورة
        </button>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="book-open" label="إجمالي الدورات" :value="$stats['total']" tone="brand" />
        <x-stat-card icon="users-round" label="إجمالي المجموعات" :value="$stats['groups']" tone="blue" />
        <x-stat-card icon="users" label="الطلاب المسجلون" :value="$stats['students']" tone="violet" />
        <x-stat-card icon="banknote" label="متوسط سعر الدورة" :value="$stats['avg_price']" tone="amber" />
    </div>

    <x-filter-bar>
        <div class="relative flex-1">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.400ms="search" class="input" placeholder="البحث باسم الدورة..." />
        </div>
        <select class="select sm:w-40" wire:model.live="levelFilter">
            <option value="">كل المستويات</option>
            @foreach ($levels as $lv)
                <option value="{{ $lv }}">{{ $lv }}</option>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($courses as $c)
                <div class="card p-5 flex flex-col gap-4" wire:key="course-{{ $c->id }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-ink-900">{{ $c->name }}</h3>
                            <p class="text-xs text-ink-400 mt-0.5">{{ $c->level ?? '—' }}</p>
                        </div>
                        <x-status-badge :label="$c->status" :tone="$statusTone[$c->status] ?? 'neutral'" />
                    </div>

                    <div class="flex items-center gap-2.5 text-sm text-ink-600">
                        <x-avatar :name="$c->teacher?->name ?? '؟'" size="sm" />
                        <span>{{ $c->teacher?->name ?? 'بدون أستاذ' }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center border-t border-ink-100 pt-4">
                        <div>
                            <p class="ltr-nums text-base font-bold text-ink-800">{{ $c->groups_count }}</p>
                            <p class="text-[11px] text-ink-400">مجموعات</p>
                        </div>
                        <div>
                            <p class="ltr-nums text-base font-bold text-ink-800">{{ $c->students_count }}</p>
                            <p class="text-[11px] text-ink-400">طالب</p>
                        </div>
                        <div>
                            <p class="ltr-nums text-base font-bold text-ink-800">{{ $c->price }}</p>
                            <p class="text-[11px] text-ink-400">MAD / شهر</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" class="btn-secondary flex-1" wire:click="openEdit({{ $c->id }})"><x-icon name="pencil" class="w-4 h-4" /> تعديل</button>
                        <button type="button" class="btn-icon" wire:click="confirmDelete({{ $c->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                    </div>
                </div>
            @empty
                <div class="card sm:col-span-2 lg:col-span-3">
                    <x-empty-state icon="book-open" title="لا توجد دورات" description="لم يتم العثور على أي دورة مطابقة لبحثك أو الفلاتر المحددة." />
                </div>
            @endforelse
        </div>

        @if ($courses->total() > 0)
            <div class="card mt-4">
                <x-livewire-pagination :paginator="$courses" />
            </div>
        @endif
    </div>

    <!-- Add / Edit modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? 'تعديل بيانات الدورة' : 'إنشاء دورة جديدة' }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">اسم الدورة</label>
                        <input type="text" wire:model="name" class="input ps-3" placeholder="مثال: English A1" />
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">المستوى</label>
                            <input type="text" wire:model="level" class="input ps-3" placeholder="مثال: مبتدئ" list="course-levels" />
                            <datalist id="course-levels">
                                @foreach ($levels as $lv)
                                    <option value="{{ $lv }}"></option>
                                @endforeach
                            </datalist>
                            @error('level') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">السعر (MAD / شهر)</label>
                            <input type="number" min="0" wire:model="price" class="input ps-3 ltr-nums" />
                            @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">الحالة</label>
                            <select class="select" wire:model="status">
                                @foreach ($statuses as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                            @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="plus" class="w-4 h-4" />
                            {{ $editingId ? 'حفظ التعديلات' : 'إنشاء الدورة' }}
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
                <h2 class="text-base font-bold text-ink-900 mb-1.5">حذف الدورة</h2>
                <p class="text-sm text-ink-500 mb-5">هل أنت متأكد من حذف هذه الدورة؟ يمكن استرجاعها لاحقاً من قبل المسؤول.</p>
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
