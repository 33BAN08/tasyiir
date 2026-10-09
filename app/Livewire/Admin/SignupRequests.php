<?php

namespace App\Livewire\Admin;

use App\Models\CenterSignupRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CenterProvisioner;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Platform back office: review center signup requests. Approval is the one
 * runtime path that creates a Tenant + owner User — an empty center, no demo
 * data. Rejection creates nothing.
 */
class SignupRequests extends Component
{
    use WithPagination;

    #[Url(as: 'status', history: true)]
    public string $tab = 'pending';

    public ?int $rejectingId = null;

    public string $rejection_reason = '';

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, CenterSignupRequest::STATUSES, true) ? $tab : 'pending';
        $this->resetPage();
    }

    public function approve(int $id): void
    {
        $request = CenterSignupRequest::pending()->findOrFail($id);

        // Someone may have registered this email since the request was filed.
        if (User::withoutGlobalScopes()->where('email', $request->owner_email)->exists()) {
            $this->dispatch('toast', message: __('يوجد حساب بهذا البريد الإلكتروني بالفعل؛ لا يمكن قبول الطلب.'));

            return;
        }

        DB::transaction(function () use ($request) {
            // Same code path as the local first-run wizard and instant signup.
            ['tenant' => $tenant] = app(CenterProvisioner::class)->provision(
                $request->center_name,
                $request->owner_name,
                $request->owner_email,
                $request->password, // already hashed at signup; the cast leaves it as-is
                $request->owner_phone,
            );

            $request->forceFill([
                'status' => 'approved',
                'tenant_id' => $tenant->id,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ])->save();
        });

        $this->dispatch('toast', message: __('تم تفعيل المركز «:name»', ['name' => $request->center_name]));
    }

    public function openReject(int $id): void
    {
        $this->rejectingId = CenterSignupRequest::pending()->findOrFail($id)->id;
        $this->rejection_reason = '';
        $this->resetValidation();
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejection_reason = '';
        $this->resetValidation();
    }

    public function reject(): void
    {
        $this->validate(
            ['rejection_reason' => ['required', 'string', 'min:3', 'max:1000']],
            [],
            ['rejection_reason' => 'سبب الرفض'],
        );

        $request = CenterSignupRequest::pending()->findOrFail($this->rejectingId);
        $request->forceFill([
            'status' => 'rejected',
            'rejection_reason' => trim($this->rejection_reason),
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ])->save();

        $this->cancelReject();
        $this->dispatch('toast', message: __('تم رفض الطلب'));
    }

    /** Kept for callers; the implementation lives on the model now. */
    public static function uniqueSlug(string $name): string
    {
        return Tenant::uniqueSlug($name);
    }

    public function render()
    {
        $counts = CenterSignupRequest::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        $requests = CenterSignupRequest::with(['reviewedBy', 'tenant'])
            ->status($this->tab)
            ->latest()
            ->paginate(15);

        return view('livewire.admin.signup-requests', [
            'requests' => $requests,
            'counts' => $counts,
            'rejecting' => $this->rejectingId ? CenterSignupRequest::find($this->rejectingId) : null,
        ])->extends('layouts.admin')->section('content')->title(__('طلبات تسجيل المراكز'));
    }
}
