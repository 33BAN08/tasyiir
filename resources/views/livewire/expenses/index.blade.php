@php
    $toneClasses = [
        'brand' => 'bg-brand-50 text-brand-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp
<div>
    <x-page-header title="المصاريف" subtitle="تتبع مصاريف ونفقات تسيير المركز">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" /> إضافة مصروف
        </button>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="receipt" label="إجمالي المصاريف هذا الشهر" :value="mad($stats['total_this_month'])" tone="rose" />
        <x-stat-card icon="layers" label="عدد الفئات" :value="$stats['categories_count']" tone="blue" />
        <x-stat-card icon="building-2" label="أكبر فئة" :value="$stats['biggest_category']" tone="amber" />
        <x-stat-card icon="calendar-days" label="المعدل اليومي" :value="mad($stats['avg_daily'])" tone="violet" />
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach ($categories as $c)
            <button type="button" wire:click="$set('categoryFilter', '{{ $categoryFilter === $c['name'] ? '' : $c['name'] }}')"
                class="card p-4 flex items-center gap-3 text-start transition-colors hover:border-brand-200 {{ $categoryFilter === $c['name'] ? 'ring-2 ring-brand-500 border-brand-300' : '' }}">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl shrink-0 {{ $toneClasses[$c['tone']] ?? $toneClasses['brand'] }}">
                    <x-icon :name="$c['icon']" class="w-5 h-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-ink-400 truncate">{{ $c['name'] }}</p>
                    <p class="ltr-nums text-sm font-bold text-ink-800">{{ mad($c['amount']) }}</p>
                </div>
            </button>
        @endforeach
    </div>

    <x-filter-bar>
        <div class="relative flex-1">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.400ms="search" class="input" placeholder="البحث في البيان..." />
        </div>
        <select class="select sm:w-40" wire:model.live="categoryFilter">
            <option value="">كل الفئات</option>
            @foreach ($categoryNames as $name)
                <option value="{{ $name }}">{{ $name }}</option>
            @endforeach
        </select>
        <select class="select sm:w-40" wire:model.live="methodFilter">
            <option value="">كل طرق الدفع</option>
            @foreach ($methods as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>
    </x-filter-bar>

    <div class="card overflow-hidden relative">
        <div wire:loading.delay class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>
        <div class="px-5 py-4 border-b border-ink-100">
            <h3 class="text-sm font-bold text-ink-800">المصاريف الأخيرة</h3>
        </div>
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">البيان</th>
                        <th class="table-head-cell">الفئة</th>
                        <th class="table-head-cell">المبلغ</th>
                        <th class="table-head-cell">طريقة الدفع</th>
                        <th class="table-head-cell">التاريخ</th>
                        <th class="table-head-cell"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($expenses as $r)
                        <tr class="hover:bg-ink-50/70 transition-colors">
                            <td class="table-cell font-semibold text-ink-800">{{ $r->label }}</td>
                            <td class="table-cell"><x-status-badge :label="$r->category" tone="neutral" :dot="false" /></td>
                            <td class="table-cell ltr-nums text-red-600 font-semibold">- {{ mad($r->amount) }}</td>
                            <td class="table-cell">{{ $r->method }}</td>
                            <td class="table-cell ltr-nums">{{ $r->date->format('Y-m-d') }}</td>
                            <td class="table-cell">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-icon" wire:click="openEdit({{ $r->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                                    <button type="button" class="btn-icon" wire:click="confirmDelete({{ $r->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="receipt" title="لا توجد مصاريف" description="لم يتم العثور على أي مصروف مطابق لبحثك أو الفلاتر المحددة." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-ink-100">
            @forelse ($expenses as $r)
                <div class="p-4">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-semibold text-ink-800">{{ $r->label }}</p>
                        <p class="ltr-nums text-red-600 font-semibold">- {{ mad($r->amount) }}</p>
                    </div>
                    <div class="flex items-center justify-between text-xs text-ink-400">
                        <span>{{ $r->category }} · {{ $r->method }}</span>
                        <span class="ltr-nums">{{ $r->date->format('Y-m-d') }}</span>
                    </div>
                    <div class="flex items-center justify-end gap-1 mt-2">
                        <button type="button" class="btn-icon" wire:click="openEdit({{ $r->id }})" aria-label="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                        <button type="button" class="btn-icon" wire:click="confirmDelete({{ $r->id }})" aria-label="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                    </div>
                </div>
            @empty
                <x-empty-state icon="receipt" title="لا توجد مصاريف" description="لم يتم العثور على أي مصروف مطابق لبحثك أو الفلاتر المحددة." />
            @endforelse
        </div>

        @if ($expenses->total() > 0)
            <x-livewire-pagination :paginator="$expenses" />
        @endif
    </div>

    <!-- Add / Edit modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? 'تعديل المصروف' : 'إضافة مصروف جديد' }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">البيان</label>
                        <input type="text" wire:model="label" class="input ps-3" placeholder="مثال: فاتورة الكهرباء" />
                        @error('label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">الفئة</label>
                            <select class="select" wire:model="category">
                                @foreach ($categoryNames as $name)
                                    <option value="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('category') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">المبلغ (MAD)</label>
                            <input type="number" min="1" wire:model="amount" class="input ps-3 ltr-nums" />
                            @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">التاريخ</label>
                            <input type="date" wire:model="date" class="input ps-3 ltr-nums" />
                            @error('date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">طريقة الدفع</label>
                            <select class="select" wire:model="method">
                                @foreach ($methods as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                            @error('method') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="plus" class="w-4 h-4" /> {{ $editingId ? 'حفظ التعديلات' : 'إضافة' }}
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
                <h2 class="text-base font-bold text-ink-900 mb-1.5">حذف المصروف</h2>
                <p class="text-sm text-ink-500 mb-5">هل أنت متأكد من حذف هذا المصروف؟ يمكن استرجاعه لاحقاً من قبل المسؤول.</p>
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
