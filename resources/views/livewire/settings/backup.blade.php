<div class="space-y-6">
    {{-- ── Database snapshots: the real backup ─────────────────────────── --}}
    @if ($dbSupported)
        <div>
            <h3 class="font-bold text-ink-800 mb-1">{{ __('نسخة قاعدة البيانات') }}</h3>
            <p class="text-xs text-ink-400 mb-4">{{ __('نسخة كاملة يمكن استرجاع المركز منها بالكامل. تُؤخذ نسخة تلقائياً مرة كل يوم وعند تشغيل البرنامج، ويُحتفظ بآخر 30 نسخة.') }}</p>

            @if (! $writable)
                <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 text-sm mb-4 flex items-start gap-2">
                    <x-icon name="triangle-alert" class="w-4 h-4 shrink-0 mt-0.5" />
                    <span>{{ __('تعذر الكتابة في مجلد النسخ الاحتياطي. صحّح المسار أدناه وإلا لن تُحفظ أي نسخة.') }}</span>
                </div>
            @elseif ($lastBackupAt)
                <p class="text-sm text-ink-600 mb-4">
                    {{ __('آخر نسخة:') }}
                    <span class="ltr-nums font-semibold text-ink-800">{{ $lastBackupAt->format('Y-m-d H:i') }}</span>
                    <span class="text-ink-400">({{ $lastBackupAt->diffForHumans() }})</span>
                </p>
            @else
                <p class="text-sm text-amber-700 mb-4">{{ __('لم تُؤخذ أي نسخة بعد.') }}</p>
            @endif

            <form wire:submit="savePath" class="flex flex-col sm:flex-row sm:items-end gap-3 mb-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('مجلد النسخ الاحتياطي') }}</label>
                    <input type="text" wire:model="backupPath" class="input ps-3 ltr-nums" dir="ltr" />
                    @error('backupPath') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-ink-400 mt-1">{{ __('يُنصح بمجلد على قرص آخر أو مفتاح USB أو مجلد Google Drive / OneDrive متزامن — نسخة على نفس القرص لا تحميك من عطب الجهاز.') }}</p>
                </div>
                <button type="submit" class="btn-secondary shrink-0"><x-icon name="check" class="w-4 h-4" /> {{ __('حفظ المسار') }}</button>
            </form>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="btn-primary" wire:click="backupNow" wire:loading.attr="disabled" wire:target="backupNow">
                    <x-icon name="download" class="w-4 h-4" /> {{ __('نسخة احتياطية الآن') }}
                </button>
            </div>

            @if ($backups->isNotEmpty())
                <div class="mt-4 rounded-xl border border-ink-200 overflow-hidden">
                    <ul class="divide-y divide-ink-100 max-h-64 overflow-y-auto">
                        @foreach ($backups as $b)
                            <li class="flex items-center gap-3 px-4 py-2.5 text-sm">
                                <span class="ltr-nums flex-1 truncate text-ink-700" dir="ltr">{{ $b['name'] }}</span>
                                <span class="ltr-nums text-xs text-ink-400">{{ number_format($b['size'] / 1024) }} KB</span>
                                <a href="{{ route('settings.backup.database', ['name' => $b['name']]) }}" class="btn-icon" title="{{ __('تنزيل النسخة') }}" aria-label="{{ __('تنزيل النسخة') }}">
                                    <x-icon name="download" class="w-4 h-4" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- ── Restore ─────────────────────────────────────────────────── --}}
        @if ($isLocal)
            <div class="pt-6 border-t border-ink-100">
                <h3 class="font-bold text-ink-800 mb-1">{{ __('استعادة نسخة احتياطية') }}</h3>
                <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 mb-4">{{ __('الاستعادة تستبدل كل البيانات الحالية ببيانات النسخة المختارة. تُؤخذ نسخة أمان من الوضع الحالي قبل الاستبدال، ويُطلب من الجميع تسجيل الدخول من جديد.') }}</p>

                <form wire:submit="restore" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('اختر نسخة محفوظة') }}</label>
                            <select wire:model="restoreName" class="select">
                                <option value="">{{ __('— اختر —') }}</option>
                                @foreach ($backups as $b)
                                    <option value="{{ $b['name'] }}">{{ $b['name'] }}</option>
                                @endforeach
                            </select>
                            @error('restoreName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('أو ارفع ملف نسخة (.sqlite)') }}</label>
                            <input type="file" wire:model="restoreFile" accept=".sqlite,.db" class="block w-full text-sm text-ink-600 file:me-3 file:rounded-lg file:border-0 file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink-700 hover:file:bg-ink-200" />
                            @error('restoreFile') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">{{ __('اكتب :word للتأكيد', ['word' => $confirmWord]) }}</label>
                        <input type="text" wire:model="restoreConfirmation" class="input ps-3" placeholder="{{ $confirmWord }}" />
                        @error('restoreConfirmation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-danger" wire:loading.attr="disabled" wire:target="restore,restoreFile">
                        <x-icon name="upload" class="w-4 h-4" /> {{ __('استعادة النسخة') }}
                    </button>
                </form>
            </div>
        @endif
    @endif

    {{-- ── Excel export ───────────────────────────────────────────────── --}}
    <div class="{{ $dbSupported ? 'pt-6 border-t border-ink-100' : '' }}">
        <h3 class="font-bold text-ink-800 mb-1">{{ __('تصدير Excel') }}</h3>
        <p class="text-xs text-ink-400 mb-4">{{ __('ملف Excel يحتوي على جميع بيانات مركزك: الطلاب، الأساتذة، الدورات، المجموعات، التسجيلات، المدفوعات، الحضور، المصاريف، والأجور — ورقة لكل قسم. للقراءة والطباعة؛ لا يمكن استرجاع المركز منه (استعمل نسخة قاعدة البيانات لذلك).') }}</p>
        <a href="{{ route('settings.backup') }}" class="btn-secondary">
            <x-icon name="download" class="w-4 h-4" /> {{ __('تنزيل ملف Excel') }}
        </a>
    </div>

    {{-- ── Student import ─────────────────────────────────────────────── --}}
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
                        @if ($enrolled > 0)
                            · <span class="text-brand-700">{{ __('و :count تسجيلاً باشتراك', ['count' => $enrolled]) }}</span>
                        @endif
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
