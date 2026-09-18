<div>
    @if ($submitted)
        <div class="text-center py-4">
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-brand-50 text-brand-600 mx-auto mb-4">
                <x-icon name="check-check" class="w-7 h-7" />
            </span>
            <h1 class="text-lg font-bold text-ink-900 mb-2">تم استلام طلبك بنجاح</h1>
            <p class="text-sm text-ink-500 mb-1">سيتم التواصل معك بعد المراجعة.</p>
            <p class="text-xs text-ink-400 mb-6">بمجرد الموافقة على الطلب يمكنك تسجيل الدخول بالبريد الإلكتروني وكلمة المرور اللذين أدخلتهما.</p>
            <a href="{{ route('login') }}" class="btn-secondary">العودة إلى تسجيل الدخول</a>
        </div>
    @else
        <h1 class="text-lg font-bold text-ink-900 mb-1">تسجيل مركز جديد</h1>
        <p class="text-sm text-ink-500 mb-6">أدخل بيانات مركزك وحساب المسؤول. يُراجع الطلب من طرف فريق TASYIIR قبل تفعيل المركز.</p>

        <form wire:submit="submit" class="space-y-4">
            <div>
                <label for="center_name" class="block text-sm font-semibold text-ink-700 mb-1.5">اسم المركز</label>
                <input wire:model="center_name" id="center_name" type="text" class="input ps-3" placeholder="مثال: مركز الأمل للتكوين" />
                @error('center_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="owner_name" class="block text-sm font-semibold text-ink-700 mb-1.5">اسم المسؤول</label>
                    <input wire:model="owner_name" id="owner_name" type="text" class="input ps-3" autocomplete="name" />
                    @error('owner_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="owner_phone" class="block text-sm font-semibold text-ink-700 mb-1.5">رقم الهاتف <span class="font-normal text-ink-400">(اختياري)</span></label>
                    <input wire:model="owner_phone" id="owner_phone" type="tel" class="input ps-3 ltr-nums" dir="ltr" autocomplete="tel" placeholder="06XX-XX-XX-XX" />
                    @error('owner_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="owner_email" class="block text-sm font-semibold text-ink-700 mb-1.5">البريد الإلكتروني (سيكون اسم الدخول)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400"><x-icon name="mail" class="w-4 h-4" /></span>
                    <input wire:model="owner_email" id="owner_email" type="email" class="input" dir="ltr" autocomplete="email" placeholder="you@example.com" />
                </div>
                @error('owner_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-ink-700 mb-1.5">كلمة المرور</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400"><x-icon name="lock" class="w-4 h-4" /></span>
                        <input wire:model="password" id="password" type="password" class="input" autocomplete="new-password" placeholder="8 أحرف على الأقل" />
                    </div>
                    @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-ink-700 mb-1.5">تأكيد كلمة المرور</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-ink-400"><x-icon name="lock" class="w-4 h-4" /></span>
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" class="input" autocomplete="new-password" placeholder="••••••••" />
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="submit">
                <x-icon name="check" class="w-4 h-4" /> إرسال طلب التسجيل
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-ink-400">لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">تسجيل الدخول</a></p>
    @endif
</div>
