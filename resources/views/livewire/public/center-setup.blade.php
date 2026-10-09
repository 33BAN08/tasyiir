<div>
    <h1 class="text-lg font-bold text-ink-900 mb-1">{{ __('مرحباً بك في TASYIIR') }}</h1>
    <p class="text-sm text-ink-500 mb-6">{{ __('أنشئ مركزك وحساب المدير. هذه الخطوة تتم مرة واحدة فقط على هذا الجهاز.') }}</p>

    <form wire:submit="submit" class="space-y-4">
        <div>
            <label for="center_name" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('اسم المركز') }}</label>
            <input wire:model="center_name" id="center_name" type="text" class="input ps-3" placeholder="{{ __('مثال: مركز النجاح للتكوين') }}" autofocus />
            @error('center_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="owner_name" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('اسم المدير') }}</label>
                <input wire:model="owner_name" id="owner_name" type="text" class="input ps-3" autocomplete="name" />
                @error('owner_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="owner_phone" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('رقم الهاتف') }}</label>
                <input wire:model="owner_phone" id="owner_phone" type="tel" class="input ps-3 ltr-nums" dir="ltr" autocomplete="tel" placeholder="06XX-XX-XX-XX" />
                @error('owner_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="owner_email" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('البريد الإلكتروني (لتسجيل الدخول)') }}</label>
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

        <div>
            <label for="locale" class="block text-sm font-semibold text-ink-700 mb-1.5">{{ __('لغة الواجهة') }}</label>
            <select wire:model="locale" id="locale" class="select">
                <option value="ar">العربية</option>
                <option value="fr">Français</option>
                <option value="en">English</option>
            </select>
            @error('locale') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center" wire:loading.attr="disabled" wire:target="submit">
            <x-icon name="check" class="w-4 h-4" />
            <span wire:loading.remove wire:target="submit">{{ __('إنشاء المركز والبدء') }}</span>
            <span wire:loading wire:target="submit">{{ __('جارٍ الإنشاء...') }}</span>
        </button>
    </form>

    <p class="mt-5 text-center text-xs text-ink-400">{{ __('يبدأ المركز فارغاً؛ تضيف الأساتذة والدورات والطلاب بنفسك بعد الدخول.') }}</p>
</div>
