<div>
    <div class="flex items-center justify-between gap-3 px-6 py-4 border-b border-ink-100">
        <div>
            <h3 class="font-bold text-ink-800">الأدوار والصلاحيات</h3>
            <p class="text-xs text-ink-400 mt-0.5">الأدوار الأساسية ثابتة؛ يمكنك إنشاء أدوار مخصصة بصلاحيات أدق لمركزك.</p>
        </div>
        <button type="button" class="btn-secondary shrink-0" wire:click="openCreate"><x-icon name="plus" class="w-4 h-4" /> دور جديد</button>
    </div>

    <div class="divide-y divide-ink-100">
        @foreach ($roles as $r)
            <div class="px-6 py-4" wire:key="role-{{ $r->id }}">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl {{ $r->is_built_in ? 'bg-violet-50 text-violet-600' : 'bg-brand-50 text-brand-600' }} shrink-0">
                        <x-icon name="shield-check" class="w-5 h-5" />
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-ink-800 text-sm">
                            {{ $r->label }}
                            @unless ($r->is_built_in) <span class="text-[11px] font-normal text-brand-600">مخصص</span> @endunless
                        </p>
                        <p class="text-xs text-ink-400">{{ $r->description }}</p>
                    </div>
                    <span class="ltr-nums text-xs font-semibold text-ink-500 shrink-0">{{ $r->users_count }} مستخدم</span>
                    @unless ($r->is_built_in)
                        <button type="button" class="btn-icon" wire:click="openEdit({{ $r->id }})" aria-label="تعديل" title="تعديل"><x-icon name="pencil" class="w-4 h-4" /></button>
                        <button type="button" class="btn-icon text-red-600" wire:click="confirmDelete({{ $r->id }})" aria-label="حذف" title="حذف"><x-icon name="trash-2" class="w-4 h-4" /></button>
                    @endunless
                </div>
                <div class="flex flex-wrap gap-1.5 mt-3 ps-[52px]">
                    @forelse ($r->permissions->sortBy(fn ($p) => array_search($p->name, array_keys($catalog))) as $p)
                        <span class="inline-flex items-center rounded-lg bg-ink-100 px-2 py-0.5 text-[11px] font-semibold text-ink-600">{{ $catalog[$p->name] ?? $p->name }}</span>
                    @empty
                        <span class="text-[11px] text-ink-400">بدون صلاحيات</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    @if ($showModal)
        <div x-data x-init="$el.querySelector('input')?.focus()" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-popover overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">{{ $editingId ? 'تعديل الدور' : 'دور جديد' }}</h2>
                    <button type="button" class="btn-icon" wire:click="closeModal" aria-label="إغلاق"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form wire:submit="save" class="p-5 max-h-[70vh] overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">اسم الدور</label>
                        <input type="text" wire:model="name" class="input ps-3" placeholder="مثال: مسؤول التسويق" />
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <p class="block text-sm font-medium text-ink-700 mb-2">الصلاحيات</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($catalog as $key => $label)
                                <label class="flex items-center gap-2.5 rounded-xl border border-ink-200 px-3 py-2.5 text-sm cursor-pointer hover:border-brand-300">
                                    <input type="checkbox" wire:model="permissions" value="{{ $key }}" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500" />
                                    <span class="text-ink-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('permissions') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" wire:click="closeModal">إلغاء</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="check" class="w-4 h-4" /> {{ $editingId ? 'حفظ التغييرات' : 'إنشاء الدور' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-950/50" wire:click="cancelDelete"></div>
            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-popover overflow-hidden p-5 text-center">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-50 text-red-600 mx-auto mb-3">
                    <x-icon name="trash-2" class="w-5 h-5" />
                </span>
                <h2 class="text-base font-bold text-ink-900 mb-1.5">حذف الدور</h2>
                <p class="text-sm text-ink-500 mb-5">هل أنت متأكد من حذف هذا الدور؟ لا يمكن حذف دور لا يزال مسنداً إلى مستخدمين.</p>
                <div class="flex items-center justify-center gap-2">
                    <button type="button" class="btn-secondary" wire:click="cancelDelete">إلغاء</button>
                    <button type="button" class="btn-danger" wire:click="delete"><x-icon name="trash-2" class="w-4 h-4" /> حذف</button>
                </div>
            </div>
        </div>
    @endif
</div>
