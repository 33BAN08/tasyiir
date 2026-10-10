<?php

namespace App\Livewire\Admin;

use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Demo;
use App\Support\Permissions;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Platform back office, online demo: every center that signed up for a free
 * trial, with the owner's contact (your sales leads), the days left and a
 * one-click "+7 days" extension.
 */
class DemoCenters extends Component
{
    use WithPagination;

    public function extend(int $id, int $days = 7): void
    {
        $tenant = Tenant::findOrFail($id);
        $settings = $tenant->settings ?? [];
        $settings['demo_extra_days'] = (int) ($settings['demo_extra_days'] ?? 0) + $days;
        $tenant->settings = $settings;
        $tenant->save();

        $this->dispatch('toast', message: __('تم تمديد تجربة «:name» بـ :days أيام', ['name' => $tenant->name, 'days' => $days]));
    }

    public function render()
    {
        $tenants = Tenant::query()->latest()->paginate(20);

        $owners = User::withoutGlobalScopes()
            ->whereIn('tenant_id', $tenants->pluck('id'))
            ->role(Permissions::OWNER_ROLE)
            ->get()
            ->keyBy('tenant_id');

        $students = Student::withoutGlobalScopes()
            ->whereIn('tenant_id', $tenants->pluck('id'))
            ->selectRaw('tenant_id, COUNT(*) as n')
            ->groupBy('tenant_id')
            ->pluck('n', 'tenant_id');

        return view('livewire.admin.demo-centers', [
            'tenants' => $tenants,
            'owners' => $owners,
            'students' => $students,
            'total' => Tenant::count(),
            'today' => Tenant::whereDate('created_at', today())->count(),
            'active' => Tenant::all()->reject(fn (Tenant $t) => Demo::endsAt($t)->isPast())->count(),
            'demo' => Demo::enabled(),
        ])->extends('layouts.admin')->section('content')->title(__('مراكز التجربة'));
    }
}
