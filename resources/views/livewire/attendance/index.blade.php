@php
    $stateTone = ['حاضر' => 'success', 'متأخر' => 'warning', 'غائب' => 'danger'];
@endphp
<div>
    <x-page-header title="الحضور" subtitle="تسجيل ومتابعة حضور الطلاب لكل حصة">
        <button type="button" class="btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save" @if ($roster->isEmpty()) disabled @endif>
            <x-icon name="check-check" class="w-4 h-4" /> حفظ الحضور
        </button>
    </x-page-header>

    <div class="card p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-ink-500 mb-1.5">التاريخ</label>
                <input type="date" wire:model.live="date" class="input ps-3 ltr-nums" />
            </div>
            <div>
                <label class="block text-xs font-medium text-ink-500 mb-1.5">المجموعة</label>
                <select wire:model.live="groupId" class="select">
                    @forelse ($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @empty
                        <option value="">لا توجد مجموعات</option>
                    @endforelse
                </select>
            </div>
            <div class="flex items-end">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink-600 w-full">
                    <span class="flex items-center gap-1.5"><x-icon name="graduation-cap" class="w-4 h-4 text-ink-400" /> {{ $group?->teacher?->name ?? '—' }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="book-open" class="w-4 h-4 text-ink-400" /> {{ $group?->course?->name ?? '—' }}</span>
                    @if ($roster->isNotEmpty())
                        <span class="text-xs {{ $savedCount ? 'text-emerald-600' : 'text-ink-400' }}">
                            {{ $savedCount ? 'محفوظ لهذا اليوم' : 'لم يُحفظ بعد لهذا اليوم' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-stat-card icon="users" label="إجمالي الطلاب" :value="$summary['total']" tone="brand" />
        <x-stat-card icon="check" label="حاضر" :value="$summary['present']" tone="blue" />
        <x-stat-card icon="clock" label="متأخر" :value="$summary['late']" tone="amber" />
        <x-stat-card icon="x" label="غائب" :value="$summary['absent']" tone="rose" />
    </div>

    <div class="card overflow-hidden relative">
        <div wire:loading.delay wire:target="groupId,date" class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center">
            <x-icon name="loader-circle" class="w-6 h-6 text-brand-600 animate-spin" />
        </div>

        @if ($roster->isNotEmpty())
            <div class="flex items-center gap-2 px-4 py-2.5 border-b border-ink-100 bg-ink-50/60 text-xs text-ink-500">
                <span>تحديد الكل:</span>
                <button type="button" class="font-semibold text-emerald-700 hover:underline" wire:click="markAll('حاضر')">حاضر</button>
                <button type="button" class="font-semibold text-amber-700 hover:underline" wire:click="markAll('متأخر')">متأخر</button>
                <button type="button" class="font-semibold text-red-700 hover:underline" wire:click="markAll('غائب')">غائب</button>
            </div>
        @endif

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-ink-50 border-b border-ink-100">
                    <tr>
                        <th class="table-head-cell">الطالب</th>
                        <th class="table-head-cell">الهاتف</th>
                        <th class="table-head-cell">الحالة</th>
                        <th class="table-head-cell">تعديل سريع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($roster as $student)
                        @php $state = $states[$student->id] ?? 'حاضر'; @endphp
                        <tr class="hover:bg-ink-50/70 transition-colors" wire:key="row-{{ $student->id }}">
                            <td class="table-cell font-semibold text-ink-800">
                                <a href="{{ route('students.show', $student) }}" class="hover:text-brand-700">{{ $student->name }}</a>
                            </td>
                            <td class="table-cell ltr-nums">{{ $student->phone }}</td>
                            <td class="table-cell"><x-status-badge :label="$state" :tone="$stateTone[$state] ?? 'neutral'" /></td>
                            <td class="table-cell">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="btn-icon {{ $state === 'حاضر' ? 'bg-emerald-50' : '' }}" wire:click="setState({{ $student->id }}, 'حاضر')" aria-label="حاضر" title="حاضر"><x-icon name="check" class="w-4 h-4 text-emerald-600" /></button>
                                    <button type="button" class="btn-icon {{ $state === 'متأخر' ? 'bg-amber-50' : '' }}" wire:click="setState({{ $student->id }}, 'متأخر')" aria-label="متأخر" title="متأخر"><x-icon name="clock" class="w-4 h-4 text-amber-600" /></button>
                                    <button type="button" class="btn-icon {{ $state === 'غائب' ? 'bg-red-50' : '' }}" wire:click="setState({{ $student->id }}, 'غائب')" aria-label="غائب" title="غائب"><x-icon name="x" class="w-4 h-4 text-red-600" /></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-ink-100">
            @foreach ($roster as $student)
                @php $state = $states[$student->id] ?? 'حاضر'; @endphp
                <div class="p-4 flex items-center justify-between gap-3" wire:key="card-{{ $student->id }}">
                    <div class="min-w-0">
                        <p class="font-semibold text-ink-800 truncate">{{ $student->name }}</p>
                        <p class="text-xs text-ink-400 ltr-nums">{{ $student->phone }}</p>
                        <div class="mt-1.5"><x-status-badge :label="$state" :tone="$stateTone[$state] ?? 'neutral'" /></div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" class="btn-icon {{ $state === 'حاضر' ? 'bg-emerald-50' : '' }}" wire:click="setState({{ $student->id }}, 'حاضر')" aria-label="حاضر"><x-icon name="check" class="w-4 h-4 text-emerald-600" /></button>
                        <button type="button" class="btn-icon {{ $state === 'متأخر' ? 'bg-amber-50' : '' }}" wire:click="setState({{ $student->id }}, 'متأخر')" aria-label="متأخر"><x-icon name="clock" class="w-4 h-4 text-amber-600" /></button>
                        <button type="button" class="btn-icon {{ $state === 'غائب' ? 'bg-red-50' : '' }}" wire:click="setState({{ $student->id }}, 'غائب')" aria-label="غائب"><x-icon name="x" class="w-4 h-4 text-red-600" /></button>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($roster->isEmpty())
            <x-empty-state icon="calendar-check" title="لا يوجد طلاب في هذه المجموعة" />
        @endif
    </div>
</div>
