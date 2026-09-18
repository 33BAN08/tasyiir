@extends('layouts.app')

@section('title', 'غير مسموح')

@section('content')
<div class="flex flex-col items-center justify-center text-center py-24 px-6">
    <span class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 mb-4">
        <x-icon name="shield-off" class="w-7 h-7" />
    </span>
    <p class="text-6xl font-extrabold text-ink-300 mb-2">403</p>
    <h1 class="text-xl font-bold text-ink-800 mb-2">لا تملك صلاحية الوصول إلى هذه الصفحة</h1>
    <p class="text-sm text-ink-500 max-w-sm mb-6">حسابك ({{ auth()->user()?->roleLabel() }}) لا يشمل هذه الوحدة. إذا كنت بحاجة إليها، اطلب من مدير المركز تعديل صلاحياتك.</p>
    <a href="/dashboard" class="btn-primary">
        <x-icon name="layout-dashboard" class="w-4 h-4" />
        العودة إلى الرئيسية
    </a>
</div>
@endsection
