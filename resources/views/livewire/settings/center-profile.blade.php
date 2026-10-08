<form wire:submit="save" @if (! $canEdit) inert @endif>
    <h3 class="font-bold text-ink-800 mb-1">{{ __('معلومات المركز') }}</h3>
    <p class="text-xs text-ink-400 mb-5">{{ __('يظهر اسم المركز وبياناته في القائمة الجانبية وعلى جميع إيصالات الأداء المطبوعة.') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('اسم المركز') }}</label>
            <input type="text" wire:model="name" class="input ps-3" placeholder="{{ __('مثال: مركز النجاح للتكوين') }}" />
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('وصف مختصر (يظهر تحت الاسم)') }}</label>
            <input type="text" wire:model="tagline" class="input ps-3" placeholder="{{ __('مثال: مركز لغات وتكوين مهني') }}" />
            @error('tagline') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('رقم الهاتف') }}</label>
            <input type="text" wire:model="phone" class="input ps-3 ltr-nums" placeholder="05XX-XX-XX-XX" />
            @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('البريد الإلكتروني') }}</label>
            <input type="email" wire:model="email" class="input ps-3" placeholder="contact@center.ma" />
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('العنوان') }}</label>
            <input type="text" wire:model="address" class="input ps-3" placeholder="{{ __('الشارع، المدينة') }}" />
            @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
    <div class="flex items-center justify-end gap-3 mt-6">
        @if ($canEdit)
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                <x-icon name="check" class="w-4 h-4" /> {{ __('حفظ التغييرات') }}
            </button>
        @else
            <p class="text-xs text-ink-400">{{ __('للاطلاع فقط — تعديل معلومات المركز متاح لمدير المركز.') }}</p>
        @endif
    </div>
</form>
