<div>
    @php $statusTone = ['نشط' => 'success', 'متوقف' => 'neutral']; @endphp

    <div class="flex items-center justify-between gap-3 px-6 py-4 border-b border-ink-100">
        <div>
            <h3 class="font-bold text-ink-800">{{ __('المستخدمون') }}</h3>
            <p class="text-xs text-ink-400 mt-0.5">{{ __('حسابات موظفي المركز. أنشئ الحساب وحدد كلمة المرور ثم شاركها مع الموظف.') }}</p>
        </div>
        <button type="button" class="btn-primary shrink-0" wire:click="openCreate"><x-icon name="plus" class="w-4 h-4" /> {{ __('إضافة مستخدم') }}</button>
    </div>

    <div class="divide-y divide-ink-100">
        @foreach ($users as $u)
            <div class="flex items-center gap-3 px-6 py-4" wire:key="user-{{ $u->id }}">
                <x-avatar :name="$u->name" size="sm" />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-ink-800 text-sm truncate">
                        {{ $u->name }}
                        @if ($u->id === auth()->id()) <span class="text-xs font-normal text-ink-400">{{ __('(أنت)') }}</span> @endif
                    </p>
                    <p class="text-xs text-ink-400 truncate">{{ __($u->roleLabel()) }} · {{ $u->email }}</p>
                </div>
                <x-status-badge :label="$u->status" :tone="$statusTone[$u->status] ?? 'neutral'" />
                @if (in_array($u->id, $editableIds, true))
                    <button type="button" class="btn-icon" wire:click="openEdit({{ $u->id }})" aria-label="{{ __('تعديل') }}" title="{{ __('تعديل') }}"><x-icon name="pencil" class="w-4 h-4" /></button>
                    <button type="button" class="btn-icon {{ $u->isActive() ? 'text-amber-600' : 'text-emerald-600' }}" wire:click="toggleStatus({{ $u->id }})" aria-label="{{ $u->isActive() ? __('إيقاف الحساب') : __('تفعيل الحساب') }}" title="{{ $u->isActive() ? __('إيقاف الحساب') : __('تفعيل الحساب') }}">
                        <x-icon :name="$u->isActive() ? 'pause' : 'play'" class="w-4 h-4" />
                    </button>
                @else
                    <span class="w-[72px] shrink-0 text-end text-[11px] text-ink-400">{{ __('مالك') }}</span>
                @endif
            </div>
        @endforeach
    </div>

    @if ($showModal)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? __('تعديل المستخدم') : __('إضافة مستخدم') }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="{{ __('إغلاق') }}"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('الاسم الكامل') }}</label>
                        <input type="text" wire:model="name" class="input ps-3" autocomplete="off" />
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('البريد الإلكتروني (لتسجيل الدخول)') }}</label>
                        <input type="email" wire:model="email" class="input ps-3" autocomplete="off" dir="ltr" />
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ $editingId ? __('كلمة مرور جديدة (اتركها فارغة للإبقاء على الحالية)') : __('كلمة المرور') }}</label>
                        <input type="password" wire:model="password" class="input ps-3" autocomplete="new-password" placeholder="{{ __('8 أحرف على الأقل') }}" />
                        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('الدور') }}</label>
                        <select wire:model="role_id" class="select">
                            <option value="">{{ __('اختر الدور…') }}</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}">{{ __($r->label) }}{{ $r->is_built_in ? '' : __(' (مخصص)') }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-ink-400 mt-1.5">{{ __('لا يمكن إنشاء حساب مدير مركز آخر من هنا.') }}</p>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">{{ __('إلغاء') }}</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="check" class="w-4 h-4" /> {{ $editingId ? __('حفظ التغييرات') : __('إضافة') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
