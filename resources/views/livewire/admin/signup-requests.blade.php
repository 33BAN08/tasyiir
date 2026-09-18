<div>
    @php $tone = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']; @endphp

    <x-page-header title="طلبات تسجيل المراكز" subtitle="راجع طلبات المراكز الجديدة؛ القبول ينشئ المركز وحساب مديره فوراً." />

    <div class="card p-2 flex gap-1 overflow-x-auto mb-6">
        @foreach (\App\Models\CenterSignupRequest::STATUS_LABELS as $key => $label)
            <button type="button" wire:click="setTab('{{ $key }}')"
                class="flex items-center gap-2 whitespace-nowrap px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $tab === $key ? 'bg-brand-50 text-brand-700' : 'text-ink-600 hover:bg-ink-100' }}">
                {{ $label }}
                <span class="ltr-nums inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full px-1.5 text-[11px] {{ $tab === $key ? 'bg-brand-600 text-white' : 'bg-ink-100 text-ink-500' }}">{{ $counts[$key] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">المركز</th>
                        <th class="table-head-cell">المسؤول</th>
                        <th class="table-head-cell">تاريخ الطلب</th>
                        <th class="table-head-cell">الحالة</th>
                        <th class="table-head-cell">المراجعة</th>
                        <th class="table-head-cell text-end">الإجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($requests as $r)
                        <tr wire:key="req-{{ $r->id }}" class="hover:bg-ink-50/70 transition-colors align-top">
                            <td class="table-cell">
                                <p class="font-semibold text-ink-800">{{ $r->center_name }}</p>
                                @if ($r->tenant) <p class="text-xs text-ink-400 ltr-nums">slug: {{ $r->tenant->slug }}</p> @endif
                            </td>
                            <td class="table-cell">
                                <p class="text-ink-800">{{ $r->owner_name }}</p>
                                <p class="text-xs text-ink-400 ltr-nums">{{ $r->owner_email }}{{ $r->owner_phone ? ' · '.$r->owner_phone : '' }}</p>
                            </td>
                            <td class="table-cell ltr-nums text-ink-600">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                            <td class="table-cell"><x-status-badge :label="$r->status_label" :tone="$tone[$r->status] ?? 'neutral'" /></td>
                            <td class="table-cell text-xs text-ink-500">
                                @if ($r->reviewed_at)
                                    {{ $r->reviewedBy?->name ?? '—' }} · <span class="ltr-nums">{{ $r->reviewed_at->format('Y-m-d H:i') }}</span>
                                    @if ($r->status === 'rejected' && $r->rejection_reason)
                                        <p class="text-red-600 mt-1">{{ $r->rejection_reason }}</p>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="table-cell text-end">
                                @if ($r->status === 'pending')
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" class="btn-primary" wire:click="approve({{ $r->id }})" wire:loading.attr="disabled" wire:target="approve({{ $r->id }})">
                                            <x-icon name="check" class="w-4 h-4" /> قبول
                                        </button>
                                        <button type="button" class="btn-danger" wire:click="openReject({{ $r->id }})">
                                            <x-icon name="x" class="w-4 h-4" /> رفض
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="inbox" title="لا توجد طلبات" description="لا توجد طلبات في هذه الحالة حالياً." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-ink-100">
            @forelse ($requests as $r)
                <div class="p-4 space-y-2" wire:key="req-m-{{ $r->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink-800">{{ $r->center_name }}</p>
                            <p class="text-xs text-ink-500">{{ $r->owner_name }}</p>
                            <p class="text-xs text-ink-400 ltr-nums truncate">{{ $r->owner_email }}</p>
                        </div>
                        <x-status-badge :label="$r->status_label" :tone="$tone[$r->status] ?? 'neutral'" />
                    </div>
                    <p class="text-xs text-ink-400 ltr-nums">{{ $r->created_at->format('Y-m-d H:i') }}</p>
                    @if ($r->reviewed_at)
                        <p class="text-xs text-ink-500">{{ $r->reviewedBy?->name ?? '—' }} · <span class="ltr-nums">{{ $r->reviewed_at->format('Y-m-d H:i') }}</span></p>
                        @if ($r->rejection_reason) <p class="text-xs text-red-600">{{ $r->rejection_reason }}</p> @endif
                    @endif
                    @if ($r->status === 'pending')
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" class="btn-primary flex-1 justify-center" wire:click="approve({{ $r->id }})"><x-icon name="check" class="w-4 h-4" /> قبول</button>
                            <button type="button" class="btn-danger flex-1 justify-center" wire:click="openReject({{ $r->id }})"><x-icon name="x" class="w-4 h-4" /> رفض</button>
                        </div>
                    @endif
                </div>
            @empty
                <x-empty-state icon="inbox" title="لا توجد طلبات" description="لا توجد طلبات في هذه الحالة حالياً." />
            @endforelse
        </div>

        @if ($requests->hasPages())
            <div class="px-5 py-3 border-t border-ink-100">
                <x-livewire-pagination :paginator="$requests" />
            </div>
        @endif
    </div>

    @if ($rejecting)
        <div x-data x-init="$el.querySelector('textarea')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="cancelReject"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">رفض طلب «{{ $rejecting->center_name }}»</h2>
                    <button type="button" class="btn-icon" wire:click="cancelReject" aria-label="إغلاق"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form wire:submit="reject" class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">سبب الرفض</label>
                        <textarea wire:model="rejection_reason" rows="3" class="input ps-3 h-auto py-2" placeholder="مثال: بيانات المركز غير مكتملة"></textarea>
                        @error('rejection_reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-xs text-ink-400">لن يُنشأ أي مركز أو حساب. يمكن لصاحب الطلب إعادة التقديم بنفس البريد لاحقاً.</p>
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" class="btn-secondary" wire:click="cancelReject">إلغاء</button>
                        <button type="submit" class="btn-danger" wire:loading.attr="disabled" wire:target="reject"><x-icon name="x" class="w-4 h-4" /> تأكيد الرفض</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
