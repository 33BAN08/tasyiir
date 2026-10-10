<div>
    <x-page-header title="{{ __('مراكز التجربة') }}" subtitle="{{ __('كل مركز سجّل في النسخة التجريبية مع رقم المسؤول للتواصل معه.') }}" />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card icon="building-2" :label="__('إجمالي المراكز')" :value="$total" />
        <x-stat-card icon="sparkles" :label="__('سجلوا اليوم')" :value="$today" />
        <x-stat-card icon="timer" :label="__('تجارب جارية')" :value="$active" />
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">{{ __('المركز') }}</th>
                        <th class="table-head-cell">{{ __('المسؤول') }}</th>
                        <th class="table-head-cell">{{ __('تاريخ التسجيل') }}</th>
                        <th class="table-head-cell">{{ __('الطلاب') }}</th>
                        <th class="table-head-cell">{{ __('التجربة') }}</th>
                        <th class="table-head-cell text-end">{{ __('الإجراء') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($tenants as $t)
                        @php
                            $owner = $owners[$t->id] ?? null;
                            $phone = $t->settings['phone'] ?? null;
                            $days = \App\Support\Demo::daysLeft($t);
                            $wa = \App\Support\Demo::whatsappTo($phone);
                        @endphp
                        <tr wire:key="t-{{ $t->id }}" class="hover:bg-ink-50/70 transition-colors">
                            <td class="table-cell font-semibold text-ink-800">{{ $t->name }}</td>
                            <td class="table-cell">
                                <p class="text-ink-800">{{ $owner?->name ?? '—' }}</p>
                                <p class="text-xs text-ink-400 ltr-nums" dir="ltr">{{ $owner?->email }}{{ $phone ? ' · '.$phone : '' }}</p>
                            </td>
                            <td class="table-cell ltr-nums text-ink-600">{{ $t->created_at->format('Y-m-d H:i') }}</td>
                            <td class="table-cell ltr-nums">{{ $students[$t->id] ?? 0 }}</td>
                            <td class="table-cell">
                                @if ($days >= 0)
                                    <x-status-badge :label="__('يتبقى :count أيام', ['count' => $days])" tone="success" />
                                @else
                                    <x-status-badge :label="__('منتهية')" tone="danger" />
                                @endif
                            </td>
                            <td class="table-cell text-end">
                                <div class="inline-flex items-center gap-1.5">
                                    @if ($wa)
                                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn-primary">
                                            <x-icon name="message-circle" class="w-4 h-4" /> {{ __('واتساب') }}
                                        </a>
                                    @endif
                                    <button type="button" class="btn-secondary" wire:click="extend({{ $t->id }})" wire:loading.attr="disabled" wire:target="extend({{ $t->id }})">
                                        <x-icon name="plus" class="w-4 h-4" /> {{ __('7 أيام') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="inbox" title="{{ __('لا توجد مراكز بعد') }}" description="{{ __('شارك رابط التجربة لتظهر هنا المراكز الجديدة.') }}" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tenants->hasPages())
            <div class="px-4 py-3 border-t border-ink-100">
                <x-livewire-pagination :paginator="$tenants" />
            </div>
        @endif
    </div>
</div>
