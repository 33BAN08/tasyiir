@php
    // Online demo: a slim bar on every tenant page with the days left and a
    // one-tap WhatsApp contact. Renders nothing outside the demo server.
    $tenant = auth()->user()?->tenant;
    $show = $tenant && \App\Support\Demo::enabled();
    $days = $show ? \App\Support\Demo::daysLeft($tenant) : null;
    $wa = $show ? \App\Support\Demo::whatsappUrl(__('السلام عليكم، جربت TASYIIR (مركز :name) وبغيت النسخة الكاملة.', ['name' => $tenant->name])) : null;
@endphp

@if ($show)
    <div class="bg-ink-950 text-white">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
            <span class="inline-flex items-center gap-2 font-semibold">
                <x-icon name="gift" class="w-4 h-4 text-brand-400" />
                {{ __('نسخة تجريبية مجانية') }}
            </span>
            <span class="text-ink-300">
                @if ($days > 0)
                    {{ __('يتبقى :count أيام', ['count' => $days]) }}
                @else
                    {{ __('آخر يوم في التجربة') }}
                @endif
                · {{ __('البيانات وهمية ويمكن تعديلها بحرية') }}
            </span>
            @if ($wa)
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="ms-auto inline-flex items-center gap-2 rounded-lg bg-brand-500 hover:bg-brand-400 px-3 py-1.5 font-semibold text-white">
                    <x-icon name="message-circle" class="w-4 h-4" />
                    {{ __('احصل على النسخة الكاملة — :price مدى الحياة', ['price' => config('tasyiir.demo.price')]) }}
                </a>
            @endif
        </div>
    </div>
@endif
