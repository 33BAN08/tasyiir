<div>
    @if ($submitted)
        <div class="text-center py-4">
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-brand-50 text-brand-600 mx-auto mb-4">
                <x-icon name="check-check" class="w-7 h-7" />
            </span>
            <h1 class="text-lg font-bold text-ink-900 mb-2">{{ __('تم استلام طلبك بنجاح') }}</h1>
            <p class="text-sm text-ink-500 mb-1">{{ __('سيتم التواصل معك بعد المراجعة.') }}</p>
            <p class="text-xs text-ink-400 mb-6">{{ __('بمجرد الموافقة على الطلب يمكنك تسجيل الدخول بالبريد الإلكتروني وكلمة المرور اللذين أدخلتهما.') }}</p>
            <a href="{{ route('login') }}" class="btn-secondary">{{ __('العودة إلى تسجيل الدخول') }}</a>
        </div>
    @else
        @if (\App\Support\Demo::enabled())
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 text-brand-700 text-xs font-bold px-3 py-1 mb-3">
                <x-icon name="gift" class="w-3.5 h-3.5" /> {{ __('تجربة مجانية :days أيام', ['days' => config('tasyiir.demo.days')]) }}
            </span>
            <h1 class="text-lg font-bold text-ink-900 mb-1">{{ __('جرّب TASYIIR مجاناً') }}</h1>
            <p class="text-sm text-ink-500 mb-6">{{ __('أنشئ حساب مركزك في دقيقة واحدة واكتشف كل الوحدات: الطلاب، الأداءات، الحضور، الجدول والتقارير. بدون بطاقة بنكية.') }}</p>
        @else
            <h1 class="text-lg font-bold text-ink-900 mb-1">{{ __('تسجيل مركز جديد') }}</h1>
            <p class="text-sm text-ink-500 mb-6">
                {{ \App\Support\Mode::signupRequiresApproval()
                    ? __('أدخل بيانات مركزك وحساب المسؤول. يُراجع الطلب من طرف فريق TASYIIR قبل تفعيل المركز.')
                    : __('أدخل بيانات مركزك وحساب المسؤول. يُفتح مركزك مباشرة بعد الإرسال.') }}
            </p>
        @endif

        <form wire:submit="submit" class="space-y-4">
            <div>
                <label for="center_name" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('اسم المركز') }}</label>
                <input wire:model="center_name" id="center_name" type="text" class="input ps-3" placeholder="{{ __('مثال: مركز الأمل للتكوين') }}" />
                @error('center_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="owner_name" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('اسم المسؤول') }}</label>
                    <input wire:model="owner_name" id="owner_name" type="text" class="input ps-3" autocomplete="name" />
                    @error('owner_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="owner_phone" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('رقم الهاتف') }} @unless (\App\Support\Demo::enabled())<span class="font-normal text-ink-400">{{ __('(اختياري)') }}</span>@endunless</label>
                    <input wire:model="owner_phone" id="owner_phone" type="tel" class="input ps-3 ltr-nums" dir="ltr" autocomplete="tel" placeholder="06XX-XX-XX-XX" />
                    @error('owner_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="owner_email" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('البريد الإلكتروني (سيكون اسم الدخول)') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400"><x-icon name="mail" class="w-4 h-4" /></span>
                    <input wire:model="owner_email" id="owner_email" type="email" class="input" dir="ltr" autocomplete="email" placeholder="you@example.com" />
                </div>
                @error('owner_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('كلمة المرور') }}</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400"><x-icon name="lock" class="w-4 h-4" /></span>
                        <input wire:model="password" id="password" type="password" class="input" autocomplete="new-password" placeholder="{{ __('8 أحرف على الأقل') }}" />
                    </div>
                    @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('تأكيد كلمة المرور') }}</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400"><x-icon name="lock" class="w-4 h-4" /></span>
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" class="input" autocomplete="new-password" placeholder="••••••••" />
                    </div>
                </div>
            </div>

            @if (\App\Support\Demo::enabled())
                <label for="sample_data" class="flex items-start gap-2.5 rounded-xl border border-ink-100 bg-ink-50/60 p-3 cursor-pointer">
                    <input wire:model="sample_data" id="sample_data" type="checkbox" class="mt-0.5 rounded border-ink-300 text-brand-600 focus:ring-brand-500" />
                    <span class="text-sm">
                        <span class="font-semibold text-ink-700">{{ __('املأ المركز ببيانات تجريبية') }}</span>
                        <span class="block text-xs text-ink-400 mt-0.5">{{ __('طلاب، أساتذة، دورات وأداءات وهمية لاكتشاف البرنامج مباشرة. يمكنك حذفها لاحقاً.') }}</span>
                    </span>
                </label>
            @endif

            <button type="submit" class="btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="submit">
                <x-icon name="check" class="w-4 h-4" wire:loading.remove wire:target="submit" />
                <span wire:loading.remove wire:target="submit">
                    {{ \App\Support\Demo::enabled() ? __('ابدأ التجربة المجانية') : (\App\Support\Mode::signupRequiresApproval() ? __('إرسال طلب التسجيل') : __('إنشاء المركز والبدء')) }}
                </span>
                <span wire:loading wire:target="submit">{{ __('جارٍ تجهيز مركزك...') }}</span>
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-ink-400">{{ __('لديك حساب بالفعل؟') }} <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ __('تسجيل الدخول') }}</a></p>
    @endif
</div>
