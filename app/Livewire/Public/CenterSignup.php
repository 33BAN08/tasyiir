<?php

namespace App\Livewire\Public;

use App\Models\CenterSignupRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Public center signup. Creates a CenterSignupRequest only — no tenant, no
 * user, no session. A platform admin turns it into a live center on approval.
 */
class CenterSignup extends Component
{
    public string $center_name = '';

    public string $owner_name = '';

    public string $owner_email = '';

    public string $owner_phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'center_name' => ['required', 'string', 'min:2', 'max:120'],
            'owner_name' => ['required', 'string', 'min:2', 'max:255'],
            'owner_email' => [
                'required', 'email', 'max:255',
                // Not an existing login, and not already waiting/approved. A rejected request may resubmit.
                Rule::unique('users', 'email'),
                Rule::unique('center_signup_requests', 'owner_email')->where(fn ($q) => $q->where('status', '!=', 'rejected')),
            ],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'center_name' => __('اسم المركز'),
            'owner_name' => __('اسم المسؤول'),
            'owner_email' => __('البريد الإلكتروني'),
            'owner_phone' => __('رقم الهاتف'),
            'password' => __('كلمة المرور'),
            'password_confirmation' => __('تأكيد كلمة المرور'),
        ];
    }

    protected function messages(): array
    {
        return [
            'owner_email.unique' => __('هذا البريد الإلكتروني مستخدم بالفعل أو لديه طلب قيد المراجعة.'),
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();

        CenterSignupRequest::create([
            'center_name' => trim($data['center_name']),
            'owner_name' => trim($data['owner_name']),
            'owner_email' => mb_strtolower(trim($data['owner_email'])),
            'owner_phone' => $data['owner_phone'] ?: null,
            'password' => Hash::make($data['password']), // never stored in clear
            'status' => 'pending',
        ]);

        $this->reset(['password', 'password_confirmation']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.center-signup')
            ->layout('layouts.guest', ['maxWidth' => 'max-w-lg'])
            ->title(__('تسجيل مركز جديد'));
    }
}
