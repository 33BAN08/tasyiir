@php
    $statusTone = ['مؤدي بالكامل' => 'success', 'دفعة جزئية' => 'warning'];
@endphp
<div>
    <x-page-header title="أداءات الطلاب" subtitle="متابعة مدفوعات ورسوم الطلاب">
        <button type="button" class="btn-primary" wire:click="openCreate">
            <x-icon name="plus" class="w-4 h-4" /> تسجيل دفعة
        </button>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="banknote" label="إجمالي المدخول" :value="mad($stats['total_revenue'])" tone="brand" />
        <x-stat-card icon="wallet" label="المدفوع هذا الشهر" :value="mad($stats['paid_this_month'])" tone="blue" />
        <x-stat-card icon="receipt" label="المبالغ المتبقية" :value="mad($stats['remaining'])" tone="amber" />
        <x-stat-card icon="triangle-alert" label="الطلاب غير المؤدين" :value="$stats['unpaid_students']" tone="rose" />
    </div>

    <x-filter-bar>
        <div class="relative flex-1">
            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="text" wire:model.live.debounce.400ms="search" class="input" placeholder="البحث باسم الطالب..." />
        </div>
        <select class="select sm:w-40" wire:model.live="methodFilter">
            <option value="">كل طرق الدفع</option>
            @foreach ($methods as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>
        <select class="select sm:w-36" wire:model.live="statusFilter">
            <option value="">كل الحالات</option>
            @foreach ($statuses as $st)
                <option value="{{ $st }}">{{ $st }}</option>
            @endforeach
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
                        <th class="table-head-cell">المبلغ</th>
                        <th class="table-head-cell">طريقة الدفع</th>
                        <th class="table-head-cell">التاريخ</th>
                        <th class="table-head-cell">الحالة</th>
                        <th class="table-head-cell"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($payments as $p)
                        <tr class="hover:bg-ink-50/70 transition-colors">
                            <td class="table-cell">
                                @if ($p->student)
                                    <a href="{{ route('students.show', $p->student) }}" class="flex items-center gap-3 group">
                                        <x-avatar :name="$p->student->name" size="sm" />
                                        <span class="font-semibold text-ink-800 group-hover:text-brand-700">{{ $p->student->name }}</span>
                                    </a>
                                @else
                                    <span class="text-ink-400">طالب محذوف</span>
                                @endif
                            </td>
                            <td class="table-cell ltr-nums font-semibold text-emerald-700">{{ mad($p->amount) }}</td>
                            <td class="table-cell">{{ $p->method }}</td>
                            <td class="table-cell ltr-nums">{{ $p->date->format('Y-m-d') }}</td>
                            <td class="table-cell"><x-status-badge :label="$p->status" :tone="$statusTone[$p->status] ?? 'neutral'" /></td>
                            <td class="table-cell">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="btn-icon" aria-label="إيصال" title="طباعة الإيصال"><x-icon name="download" class="w-4 h-4" /></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="wallet" title="لا توجد مدفوعات" description="لم يتم العثور على أي دفعة مطابقة لبحثك أو الفلاتر المحددة." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-ink-100">
            @forelse ($payments as $p)
                <div class="p-4 flex items-center gap-3">
                    <x-avatar :name="$p->student?->name ?? '؟'" size="sm" />
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-ink-800 truncate">{{ $p->student?->name ?? 'طالب محذوف' }}</p>
                        <p class="text-xs text-ink-400 ltr-nums">{{ $p->date->format('Y-m-d') }} · {{ $p->method }}</p>
                    </div>
                    <div class="text-end">
                        <p class="ltr-nums font-bold text-emerald-700">{{ mad($p->amount) }}</p>
                        <x-status-badge :label="$p->status" :tone="$statusTone[$p->status] ?? 'neutral'" />
                    </div>
                    <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="btn-icon shrink-0" aria-label="إيصال"><x-icon name="download" class="w-4 h-4" /></a>
                </div>
            @empty
                <x-empty-state icon="wallet" title="لا توجد مدفوعات" description="لم يتم العثور على أي دفعة مطابقة لبحثك أو الفلاتر المحددة." />
            @endforelse
        </div>

        @if ($payments->total() > 0)
            <x-livewire-pagination :paginator="$payments" />
        @endif
    </div>

    <!-- Record payment modal -->
    @if ($showModal)
        <div x-data x-init="$el.querySelector('select')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">تسجيل دفعة جديدة</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">الطالب</label>
                        <select class="select" wire:model.live="student_id">
                            <option value="">اختر طالباً عليه رسوم مستحقة</option>
                            @foreach ($debtors as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} — المتبقي {{ mad($s->currentEnrollment->remaining) }}</option>
                            @endforeach
                        </select>
                        @if ($debtors->isEmpty())
                            <p class="text-[11px] text-ink-400 mt-1">كل الطلاب مؤدون حالياً.</p>
                        @endif
                        @error('student_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">المبلغ (MAD)</label>
                            <input type="number" min="1" wire:model="amount" class="input ps-3 ltr-nums" />
                            @if ($student_id)
                                <p class="text-[11px] text-ink-400 mt-1">المتبقي على الطالب: <span class="ltr-nums">{{ mad($outstanding) }}</span></p>
                            @endif
                            @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">التاريخ</label>
                        <input type="date" wire:model="date" class="input ps-3 ltr-nums" />
                        @error('date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="wallet" class="w-4 h-4" /> تسجيل الدفعة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
