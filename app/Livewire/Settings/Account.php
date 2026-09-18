<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

/** The signed-in user's own account: name, email, and an optional password change. */
class Account extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->id())],
            // Changing the password requires proving the current one.
            'current_password' => [Rule::requiredIf(fn () => $this->password !== ''), 'nullable', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected array $validationAttributes = [
        'name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'current_password' => 'كلمة المرور الحالية',
        'password' => 'كلمة المرور الجديدة',
        'password_confirmation' => 'تأكيد كلمة المرور',
    ];

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function save(): void
    {
        $data = $this->validate();

        $user = auth()->user();
        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($data['password'] !== null && $data['password'] !== '') {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->dispatch('toast', message: 'تم حفظ بيانات الحساب بنجاح');
    }

    public function render()
    {
        return view('livewire.settings.account');
    }
}
