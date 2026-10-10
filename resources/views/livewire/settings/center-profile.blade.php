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
            <label for="center-phone" class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('رقم الهاتف') }}</label>
            <input type="text" id="center-phone" wire:model.live.debounce.500ms="phone" class="input ps-3 ltr-nums" placeholder="05XX-XX-XX-XX" />
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

    <div class="mt-8 pt-6 border-t border-ink-100">
        <h3 class="flex items-center gap-2 text-sm font-bold text-ink-800 mb-1">
            <x-icon name="message-circle" class="w-4 h-4 text-emerald-600" />
            {{ __('رسائل واتساب لأولياء الأمور') }}
        </h3>
        <p class="text-xs text-ink-500 mb-4">{{ __('النص الذي يُفتح في واتساب عند إشعار ولي الأمر بالغياب أو التأخر.') }}</p>

        <div class="rounded-xl bg-ink-50 px-4 py-3 mb-5">
            <p class="text-xs font-semibold text-ink-600 mb-1.5">{{ __('المتغيرات المتاحة') }}</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($placeholders as $p)
                    <code class="rounded-md bg-white border border-ink-200 px-1.5 py-0.5 text-[11px] font-mono text-ink-700">{{ $p }}</code>
                @endforeach
            </div>
        </div>

        @foreach ([
            ['field' => 'absence_message', 'state' => 'غائب', 'label' => __('رسالة الغياب'), 'preview' => $absencePreview],
            ['field' => 'late_message', 'state' => 'متأخر', 'label' => __('رسالة التأخر'), 'preview' => $latePreview],
        ] as $tpl)
            <div class="mb-5">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-ink-700">{{ $tpl['label'] }}</label>
                    @if ($canEdit)
                        <button type="button" class="text-xs font-semibold text-brand-600 hover:text-brand-700"
                                wire:click="resetTemplate('{{ $tpl['state'] }}')">
                            {{ __('إعادة النص الافتراضي') }}
                        </button>
                    @endif
                </div>
                <textarea wire:model.live.debounce.500ms="{{ $tpl['field'] }}" rows="3" class="input ps-3 py-2 leading-relaxed"
                          @unless ($canEdit) disabled @endunless></textarea>
                @error($tpl['field']) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                <div class="mt-2 rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-2.5">
                    <p class="text-[11px] font-semibold text-emerald-800 mb-1">{{ __('معاينة') }}</p>
                    <p class="text-xs text-ink-700 leading-relaxed whitespace-pre-line">{{ $tpl['preview'] }}</p>
                </div>

                @unless ($hasPhone)
                    <p class="mt-1.5 text-[11px] text-amber-700">
                        <a href="#center-phone"
                           x-on:click.prevent="document.getElementById('center-phone')?.focus()"
                           class="font-semibold underline hover:text-amber-800">
                            {{ __('أضف رقم هاتف المركز ليظهر في الرسالة') }}
                        </a>
                    </p>
                @endunless
            </div>
        @endforeach

        <p class="text-[11px] text-ink-400">
            {{ __('عند ترك النص كما هو افتراضياً، تتكيّف الصيغة تلقائياً مع جنس الطالب (ابنكم / ابنتكم).') }}
        </p>
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
