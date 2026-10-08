<div class="space-y-6">
    <div>
        <h3 class="font-bold text-ink-800 mb-1">{{ __('النسخ الاحتياطي') }}</h3>
        <p class="text-xs text-ink-400 mb-4">{{ __('ملف Excel يحتوي على جميع بيانات مركزك: الطلاب، الأساتذة، الدورات، المجموعات، التسجيلات، المدفوعات، الحضور، المصاريف، والأجور — ورقة لكل قسم. احتفظ بنسخة خارج هذا الجهاز (USB أو سحابة).') }}</p>
        <a href="{{ route('settings.backup') }}" class="btn-primary">
            <x-icon name="download" class="w-4 h-4" /> {{ __('تنزيل نسخة احتياطية') }}
        </a>
    </div>

    <div class="pt-6 border-t border-ink-100">
        <h3 class="font-bold text-ink-800 mb-1">{{ __('استيراد الطلاب') }}</h3>
        <p class="text-xs text-ink-400 mb-1">{{ __('انقل قائمة طلابك من Excel بدل إعادة كتابتها: نزّل القالب، املأه، ثم ارفعه هنا.') }}</p>
        <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 mb-4">{{ __('يستورد هذا القسم الطلاب فقط. التسجيلات والمدفوعات وسجل الحضور لا تُستورد؛ تُدخل من وحداتها بعد استيراد الطلاب.') }}</p>

        <ol class="text-sm text-ink-600 space-y-1.5 mb-4 list-decimal ps-5">
            <li>
                <a href="{{ route('settings.backup.template') }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ __('تنزيل قالب Excel') }}</a>
                <span class="text-ink-400">— {{ __('الأعمدة:') }} {{ implode('، ', $headings) }}</span>
            </li>
            <li>{{ __('اكتب اسم الدورة والمجموعة كما هما مسجلان في النظام تماماً؛ الصف الذي لا يطابق دورة أو مجموعة موجودة يُرفض ولا يُنشئ شيئاً.') }}</li>
            <li>{{ __('الاسم والهاتف إلزاميان؛ باقي الأعمدة اختيارية. تاريخ التسجيل بالصيغة YYYY-MM-DD أو DD/MM/YYYY (يُستعمل تاريخ اليوم إن تُرك فارغاً).') }}</li>
        </ol>

        <form wire:submit="import" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('ملف الاستيراد (.xlsx)') }}</label>
                <input type="file" wire:model="file" accept=".xlsx,.xls" class="block w-full text-sm text-ink-600 file:me-3 file:rounded-lg file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200" />
                @error('file') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-ink-400 mt-1" wire:loading wire:target="file">{{ __('جارٍ رفع الملف...') }}</p>
            </div>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="import,file">
                <x-icon name="upload" class="w-4 h-4" /> {{ __('استيراد الطلاب') }}
            </button>
        </form>

        @if ($imported !== null)
            <div class="mt-5 rounded-xl border border-ink-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-ink-50">
                    <p class="text-sm font-semibold text-ink-800">
                        {{ __('تم استيراد :count طالباً', ['count' => $imported]) }}
                        @if (count($failures))
                            · <span class="text-red-600">{{ __(':count صفاً لم يُستورد', ['count' => count($failures)]) }}</span>
                        @endif
                    </p>
                    <button type="button" class="btn-icon" wire:click="clearResult" aria-label="{{ __('إغلاق') }}"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                @if (count($failures))
                    <ul class="divide-y divide-ink-100 max-h-72 overflow-y-auto">
                        @foreach ($failures as $f)
                            <li class="px-4 py-2 text-sm flex gap-3">
                                <span class="ltr-nums shrink-0 font-semibold text-ink-500">{{ __('الصف :row', ['row' => $f['row']]) }}</span>
                                <span class="text-ink-700">{{ $f['message'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>
</div>
