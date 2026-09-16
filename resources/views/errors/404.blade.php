@extends('layouts.app')

@section('title', 'الصفحة غير موجودة')

@section('content')
<div class="flex flex-col items-center justify-center text-center py-24 px-6">
    <p class="text-6xl font-extrabold text-brand-600 mb-2">404</p>
    <h1 class="text-xl font-bold text-ink-800 mb-2">الصفحة غير موجودة</h1>
    <p class="text-sm text-ink-500 max-w-sm mb-6">الصفحة التي تحاول الوصول إليها غير موجودة أو تم نقلها.</p>
    <a href="/dashboard" class="btn-primary">
        <x-icon name="layout-dashboard" class="w-4 h-4" />
        العودة إلى الرئيسية
    </a>
</div>
@endsection
