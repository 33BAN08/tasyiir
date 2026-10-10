<?php

namespace App\Livewire\Settings;

use App\Support\AbsenceMessage;
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

    /** WhatsApp texts sent to parents. Empty = use the built-in default. */
    public string $absence_message = '';

    public string $late_message = '';

    protected array $rules = [
        'name' => ['required', 'string', 'min:2', 'max:120'],
        'tagline' => ['nullable', 'string', 'max:120'],
        'phone' => ['nullable', 'string', 'max:30'],
        'email' => ['nullable', 'email', 'max:255'],
        'address' => ['nullable', 'string', 'max:255'],
        'absence_message' => ['nullable', 'string', 'max:1000'],
        'late_message' => ['nullable', 'string', 'max:1000'],
    ];

    protected function validationAttributes(): array
    {
        return [
            'name' => __('اسم المركز'),
            'tagline' => __('الوصف المختصر'),
            'phone' => __('رقم الهاتف'),
            'email' => __('البريد الإلكتروني'),
            'address' => __('العنوان'),
            'absence_message' => __('رسالة الغياب'),
            'late_message' => __('رسالة التأخر'),
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
        // Blank in the form means "keep the built-in text"; the owner is shown
        // the default so they can edit it rather than start from nothing.
        $this->absence_message = (string) ($tenant->setting('absence_message') ?: $this->defaultTemplate('غائب'));
        $this->late_message = (string) ($tenant->setting('late_message') ?: $this->defaultTemplate('متأخر'));
    }

    public function hasPhone(): bool
    {
        return trim($this->phone) !== '';
    }

    /** The built-in text as it would really be sent with the phone currently entered. */
    protected function defaultTemplate(string $state): string
    {
        return AbsenceMessage::defaultTemplate($state, null, $this->hasPhone());
    }

    /** True while the owner has not written their own wording. */
    protected static function isDefault(string $value, string $state): bool
    {
        $value = trim($value);

        return $value === ''
            || $value === AbsenceMessage::defaultTemplate($state, null, true)
            || $value === AbsenceMessage::defaultTemplate($state, null, false);
    }

    /**
     * Typing (or clearing) the center's phone changes what the built-in text
     * says, so a template the owner has not touched follows it. One they wrote
     * themselves is left exactly as written.
     */
    public function updatedPhone(): void
    {
        foreach (['غائب' => 'absence_message', 'متأخر' => 'late_message'] as $state => $field) {
            if (self::isDefault($this->{$field}, $state)) {
                $this->{$field} = $this->defaultTemplate($state);
            }
        }
    }

    public function resetTemplate(string $state): void
    {
        if ($state === 'متأخر') {
            $this->late_message = $this->defaultTemplate('متأخر');
        } else {
            $this->absence_message = $this->defaultTemplate('غائب');
        }
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
            // Storing null when the text is the built-in one keeps the default
            // alive: improve the wording in a later version and every center
            // that never customised it gets the improvement.
            'absence_message' => self::storedTemplate($data['absence_message'] ?? '', 'غائب'),
            'late_message' => self::storedTemplate($data['late_message'] ?? '', 'متأخر'),
        ]);
        $tenant->save();

        $this->dispatch('toast', message: __('تم حفظ معلومات المركز بنجاح'));
    }

    protected static function storedTemplate(string $value, string $state): ?string
    {
        return self::isDefault($value, $state) ? null : trim($value);
    }

    public function render()
    {
        $tenant = auth()->user()->tenant;

        // The preview runs the message through AbsenceMessage exactly as the
        // WhatsApp button does, with the phone currently in the form rather
        // than the saved one — so what the owner reads here is what a parent
        // will read, including the clause that disappears without a phone.
        $phone = trim($this->phone);

        return view('livewire.settings.center-profile', [
            'canEdit' => $this->canEdit(),
            'hasPhone' => $this->hasPhone(),
            'placeholders' => AbsenceMessage::PLACEHOLDERS,
            'absencePreview' => AbsenceMessage::preview('غائب', $tenant, $this->absence_message, $phone),
            'latePreview' => AbsenceMessage::preview('متأخر', $tenant, $this->late_message, $phone),
        ]);
    }
}
