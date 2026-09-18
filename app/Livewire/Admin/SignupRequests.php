<?php

namespace App\Livewire\Admin;

use App\Models\CenterSignupRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            $this->dispatch('toast', message: 'يوجد حساب بهذا البريد الإلكتروني بالفعل؛ لا يمكن قبول الطلب.');

            return;
        }

        DB::transaction(function () use ($request) {
            $tenant = Tenant::create([
                'name' => $request->center_name,
                'slug' => self::uniqueSlug($request->center_name),
                'settings' => [],
            ]);

            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                // Hashed at signup. The 'hashed' cast leaves an existing hash untouched (Hash::isHashed).
                'password' => $request->password,
                'status' => 'نشط',
                'email_verified_at' => now(),
            ]);
            $owner->assignRole(Permissions::OWNER_ROLE);

            $request->forceFill([
                'status' => 'approved',
                'tenant_id' => $tenant->id,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ])->save();
        });

        $this->dispatch('toast', message: "تم تفعيل المركز «{$request->center_name}»");
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
        $this->dispatch('toast', message: 'تم رفض الطلب');
    }

    /** ASCII slug from the (usually Arabic) center name, suffixed on collision. */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-', 'ar') ?: 'center';
        $slug = $base;
        for ($i = 2; Tenant::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
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
        ])->extends('layouts.admin')->section('content')->title('طلبات تسجيل المراكز');
    }
}
