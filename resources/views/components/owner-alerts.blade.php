@php
    // Owner-only health warnings for a local install: nobody else is watching
    // the backups, and an expiring licence should never be a surprise.
    $user = auth()->user();
    $show = $user?->can('manage-settings') && \App\Support\Mode::isLocal();
    $backup = $show ? app(\App\Services\DatabaseBackup::class) : null;
    $license = $show ? app(\App\Services\License::class) : null;
    $backupStale = $backup?->isStale();
    $licenseStatus = $license?->status();
    $licenseWarning = $license?->isExpiringSoon();
@endphp

@if ($show && ($backupStale || $licenseWarning))
    <div class="space-y-3 mb-6">
        @if ($backupStale)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex items-start gap-3">
                <x-icon name="triangle-alert" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0 text-sm">
                    <p class="font-semibold text-amber-800">
                        {{ $backup->isWritable()
                            ? __('لم تُؤخذ نسخة احتياطية منذ أكثر من 3 أيام')
                            : __('تعذر الكتابة في مجلد النسخ الاحتياطي') }}
                    </p>
                    <p class="text-amber-700 mt-0.5">{{ __('بيانات مركزك موجودة على هذا الجهاز فقط. افتح الإعدادات لأخذ نسخة الآن واختيار مجلد آمن.') }}</p>
                </div>
                <a href="{{ route('settings.index', ['tab' => 'backup']) }}" class="btn-secondary shrink-0">{{ __('النسخ الاحتياطي') }}</a>
            </div>
        @endif

        @if ($licenseWarning)
            <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 flex items-start gap-3">
                <x-icon name="timer" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0 text-sm">
                    <p class="font-semibold text-blue-800">
                        {{ $licenseStatus['state'] === \App\Services\License::TRIAL
                            ? __('تنتهي الفترة التجريبية بعد :count يوماً', ['count' => $licenseStatus['days_left']])
                            : __('ينتهي الترخيص بعد :count يوماً', ['count' => $licenseStatus['days_left']]) }}
                    </p>
                    <p class="text-blue-700 mt-0.5">{{ __('تواصل مع IAM Agency لتجديد الترخيص قبل انتهاء المدة.') }}</p>
                </div>
                <a href="{{ route('settings.index', ['tab' => 'license']) }}" class="btn-secondary shrink-0">{{ __('الترخيص') }}</a>
            </div>
        @endif
    </div>
@endif
