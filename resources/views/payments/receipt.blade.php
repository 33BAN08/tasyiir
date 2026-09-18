<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>إيصال أداء رقم {{ $number }} · {{ $tenant?->name ?? 'TASYIIR' }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A5; margin: 12mm; }
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none !important; border: none !important; margin: 0 !important; max-width: none !important; }
        }
    </style>
</head>
<body class="bg-ink-50 font-sans text-ink-800 antialiased min-h-screen py-8 px-4">
    <div class="no-print max-w-lg mx-auto mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('payments.index') }}" class="btn-secondary">
            <x-icon name="arrow-right" class="w-4 h-4" /> رجوع
        </a>
        <button type="button" class="btn-primary" onclick="window.print()">
            <x-icon name="download" class="w-4 h-4" /> طباعة / حفظ PDF
        </button>
    </div>

    <div class="receipt max-w-lg mx-auto bg-white border border-ink-100 rounded-2xl shadow-card p-8">
        <div class="flex items-start justify-between gap-4 pb-5 border-b border-ink-100">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-brand-600 text-white font-extrabold text-xl shrink-0">{{ mb_substr($tenant?->name ?? 'T', 0, 1) }}</span>
                <div class="leading-tight">
                    <p class="font-extrabold text-lg text-ink-900">{{ $tenant?->name ?? 'TASYIIR' }}</p>
                    @if ($tenant?->setting('tagline'))
                        <p class="text-[11px] text-ink-500">{{ $tenant->setting('tagline') }}</p>
                    @endif
                    @if ($tenant?->setting('phone') || $tenant?->setting('address'))
                        <p class="text-[11px] text-ink-400 mt-0.5">
                            @if ($tenant->setting('address')){{ $tenant->setting('address') }}@endif
                            @if ($tenant->setting('phone')) · <span class="ltr-nums">{{ $tenant->setting('phone') }}</span>@endif
                        </p>
                    @endif
                </div>
            </div>
            <div class="text-end">
                <p class="text-xs text-ink-400">إيصال أداء</p>
                <p class="ltr-nums font-mono font-bold text-ink-900">#{{ $number }}</p>
                <p class="ltr-nums text-xs text-ink-500 mt-0.5">{{ $payment->date->format('Y-m-d') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-x-6 gap-y-3 py-5 border-b border-ink-100 text-sm">
            <div>
                <p class="text-xs text-ink-400 mb-0.5">الطالب</p>
                <p class="font-semibold text-ink-900">{{ $student?->name ?? 'طالب محذوف' }}</p>
                @if ($student?->phone)
                    <p class="ltr-nums text-xs text-ink-500">{{ $student->phone }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs text-ink-400 mb-0.5">الدورة / المجموعة</p>
                <p class="font-semibold text-ink-900">{{ $enrollment?->course?->name ?? '—' }}</p>
                <p class="text-xs text-ink-500">{{ $enrollment?->group?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink-400 mb-0.5">طريقة الدفع</p>
                <p class="font-semibold text-ink-900">{{ $payment->method }}</p>
            </div>
            <div>
                <p class="text-xs text-ink-400 mb-0.5">تاريخ الاستحقاق التالي</p>
                <p class="ltr-nums font-semibold text-ink-900">{{ $enrollment?->due_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
        </div>

        <div class="py-5 border-b border-ink-100">
            <div class="flex items-center justify-between rounded-xl bg-brand-50 px-5 py-4">
                <span class="text-sm font-semibold text-brand-800">المبلغ المؤدى</span>
                <span class="ltr-nums text-2xl font-extrabold text-brand-700">{{ mad($payment->amount) }}</span>
            </div>
        </div>

        @if ($enrollment)
            <table class="w-full text-sm mt-5">
                <tbody class="divide-y divide-ink-100">
                    <tr><td class="py-2 text-ink-500">سعر الدورة (شهرياً)</td><td class="py-2 text-end ltr-nums font-semibold">{{ mad($enrollment->price) }}</td></tr>
                    @if ($enrollment->discount > 0)
                        <tr><td class="py-2 text-ink-500">الخصم</td><td class="py-2 text-end ltr-nums font-semibold text-emerald-700">- {{ mad($enrollment->discount) }}</td></tr>
                    @endif
                    <tr><td class="py-2 text-ink-500">الصافي المستحق</td><td class="py-2 text-end ltr-nums font-semibold">{{ mad($enrollment->net) }}</td></tr>
                    <tr><td class="py-2 text-ink-500">مجموع المدفوع إلى تاريخه</td><td class="py-2 text-end ltr-nums font-semibold text-emerald-700">{{ mad($paidToDate) }}</td></tr>
                    <tr>
                        <td class="py-2 font-semibold text-ink-800">المتبقي</td>
                        <td class="py-2 text-end ltr-nums font-extrabold {{ $enrollment->remaining > 0 ? 'text-red-600' : 'text-emerald-700' }}">{{ mad($enrollment->remaining) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="mt-6 flex items-center justify-between">
            <x-status-badge :label="$payment->status" :tone="$payment->status === 'مؤدي بالكامل' ? 'success' : 'warning'" />
            <div class="text-center">
                <div class="w-40 border-b border-ink-300 mb-1"></div>
                <p class="text-[11px] text-ink-400">توقيع وختم المركز</p>
            </div>
        </div>

        <p class="mt-6 text-center text-[11px] text-ink-400">شكراً لثقتكم — {{ $tenant?->name ?? 'TASYIIR' }} · طُبع في <span class="ltr-nums">{{ now()->format('Y-m-d H:i') }}</span></p>
        <p class="mt-1 text-center text-[10px] text-ink-300">TASYIIR · IAM Agency</p>
    </div>
</body>
</html>
