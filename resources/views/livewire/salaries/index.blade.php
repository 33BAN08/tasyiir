@php
    $statusTone = ['مدفوع' => 'success', 'مدفوع جزئياً' => 'warning', 'غير مدفوع' => 'danger'];
    $basis = fn ($r) => $r->teacher->isCommissionBased()
        ? number_format((int) $r->teacher->commission_rate).'% من مدفوعات طلابه هذا الشهر'
        : 'راتب ثابت';
@endphp
<div>
    <x-page-header title="أجور الأساتذة" subtitle="حساب وتتبع أجور فريق التدريس — رصيد جارٍ لكل أستاذ">
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card icon="banknote" label="إجمالي الأجور" :value="mad($stats['total'])" tone="brand" />
        <x-stat-card icon="wallet" label="المدفوع" :value="mad($stats['paid'])" tone="blue" />
        <x-stat-card icon="receipt" label="المتبقي" :value="mad($stats['remaining'])" tone="rose" />
    </div>

    <div class="card overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">الأستاذ</th>
                        <th class="table-head-cell">الساعات</th>
                        <th class="table-head-cell">السعر / ساعة</th>
                        <th class="table-head-cell">الراتب</th>
                        <th class="table-head-cell">المدفوع</th>
                        <th class="table-head-cell">المتبقي</th>
                        <th class="table-head-cell">الحالة</th>
                        <th class="table-head-cell"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($rows as $r)
                        <tr class="hover:bg-ink-50/70 transition-colors" wire:key="salary-{{ $r->id }}">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$r->teacher->name" size="sm" />
                                    <div>
                                        <p class="font-semibold text-ink-800">{{ $r->teacher->name }}</p>
                                        <p class="text-xs text-ink-400">{{ $r->teacher->specialty ?? '—' }} · {{ $basis($r) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell ltr-nums">{{ $r->hours }} س</td>
                            <td class="table-cell ltr-nums">{{ $r->rate !== null ? mad($r->rate) : '—' }}</td>
                            <td class="table-cell ltr-nums font-semibold text-ink-800">{{ mad($r->salary) }}</td>
                            <td class="table-cell ltr-nums text-emerald-700">{{ mad($r->paid) }}</td>
                            <td class="table-cell ltr-nums text-red-600">{{ mad($r->remaining) }}</td>
                            <td class="table-cell"><x-status-badge :label="$r->status" :tone="$statusTone[$r->status] ?? 'neutral'" /></td>
                            <td class="table-cell">
                                <div class="flex items-center justify-end">
                                    <button type="button" class="btn-secondary !py-1.5 !px-3 text-xs" wire:click="openPay({{ $r->teacher_id }})" @if ($r->remaining <= 0) disabled @endif>
                                        <x-icon name="wallet" class="w-3.5 h-3.5" /> تسجيل دفعة
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state icon="banknote" title="لا يوجد أساتذة" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-ink-100">
            @forelse ($rows as $r)
                <div class="p-4" wire:key="salary-card-{{ $r->id }}">
                    <div class="flex items-center gap-3 mb-2">
                        <x-avatar :name="$r->teacher->name" size="sm" />
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-ink-800 truncate">{{ $r->teacher->name }}</p>
                            <p class="text-xs text-ink-400 truncate">{{ $basis($r) }}</p>
                        </div>
                        <x-status-badge :label="$r->status" :tone="$statusTone[$r->status] ?? 'neutral'" />
                    </div>
                    <div class="flex items-center justify-between text-xs text-ink-500 ltr-nums">
                        <span>{{ $r->hours }} س</span>
                        <span>الراتب: {{ mad($r->salary) }}</span>
                        <span>المتبقي: {{ mad($r->remaining) }}</span>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="button" class="btn-secondary !py-1.5 !px-3 text-xs" wire:click="openPay({{ $r->teacher_id }})" @if ($r->remaining <= 0) disabled @endif>
                            <x-icon name="wallet" class="w-3.5 h-3.5" /> تسجيل دفعة
                        </button>
                    </div>
                </div>
            @empty
                <x-empty-state icon="banknote" title="لا يوجد أساتذة" />
            @endforelse
        </div>
    </div>

    <!-- Record salary payment -->
    @if ($paying)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closePay"></div>
            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">صرف أجرة — {{ $paying->teacher->name }}</h2>
                    <button type="button" class="btn-icon" wire:click="closePay" aria-label="إغلاق"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form wire:submit="recordPayment" class="p-5 space-y-4">
                    <div class="grid grid-cols-3 gap-2 text-center rounded-xl bg-ink-50 p-3">
                        <div><p class="text-[11px] text-ink-400">الراتب</p><p class="ltr-nums text-sm font-bold text-ink-800">{{ mad($paying->salary) }}</p></div>
                        <div><p class="text-[11px] text-ink-400">المدفوع</p><p class="ltr-nums text-sm font-bold text-emerald-600">{{ mad($paying->paid) }}</p></div>
                        <div><p class="text-[11px] text-ink-400">المتبقي</p><p class="ltr-nums text-sm font-bold text-red-600">{{ mad($paying->remaining) }}</p></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">المبلغ (MAD)</label>
                        <input type="number" min="1" max="{{ $paying->remaining }}" wire:model="payAmount" class="input ps-3 ltr-nums" />
                        @error('payAmount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" class="btn-secondary" wire:click="closePay">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="recordPayment">
                            <x-icon name="wallet" class="w-4 h-4" /> تسجيل الدفعة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
