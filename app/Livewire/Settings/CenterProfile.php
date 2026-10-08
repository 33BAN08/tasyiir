<?php

namespace App\Livewire\Settings;

use Livewire\Component;

/**
 * The tenant's public identity: shown in the sidebar/footer and printed on
 * every receipt. Name lives on tenants.name; the rest in tenants.settings.
 */
class CenterProfile extends Component
{
    public string $name = '';

    public string $tagline = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    protected array $rules = [
        'name' => ['required', 'string', 'min:2', 'max:120'],
        'tagline' => ['nullable', 'string', 'max:120'],
        'phone' => ['nullable', 'string', 'max:30'],
        'email' => ['nullable', 'email', 'max:255'],
        'address' => ['nullable', 'string', 'max:255'],
    ];

    protected function validationAttributes(): array
    {
        return [
            'name' => __('اسم المركز'),
            'tagline' => __('الوصف المختصر'),
            'phone' => __('رقم الهاتف'),
            'email' => __('البريد الإلكتروني'),
            'address' => __('العنوان'),
        ];
    }

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;
        $this->name = $tenant->name;
        $this->tagline = (string) $tenant->setting('tagline');
        $this->phone = (string) $tenant->setting('phone');
        $this->email = (string) $tenant->setting('email');
        $this->address = (string) $tenant->setting('address');
    }

    /** Anyone may read the center's details; only manage-settings may change them. */
    public function canEdit(): bool
    {
        return auth()->user()->can('manage-settings');
    }

    public function save(): void
    {
        abort_unless($this->canEdit(), 403);
        $data = $this->validate();

        $tenant = auth()->user()->tenant;
        $tenant->name = $data['name'];
        $tenant->settings = array_merge($tenant->settings ?? [], [
            'tagline' => $data['tagline'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'address' => $data['address'] ?: null,
        ]);
        $tenant->save();

        $this->dispatch('toast', message: __('تم حفظ معلومات المركز بنجاح'));
    }

    public function render()
    {
        return view('livewire.settings.center-profile', ['canEdit' => $this->canEdit()]);
    }
}
