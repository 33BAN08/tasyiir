<?php

namespace App\Livewire\Settings;

use App\Services\License as LicenseService;
use App\Support\Mode;
use Livewire\Component;

/**
 * Settings → الترخيص: the machine code to send to the vendor, the current
 * licence state, and a box to paste the licence issued for this machine.
 * Reachable even when the licence has expired — that is the whole point.
 */
class License extends Component
{
    public string $licence = '';

    public function mount(): void
    {
        abort_unless(Mode::isLocal(), 404);
        abort_unless(auth()->user()->can('manage-settings'), 403);
    }

    public function activate(): void
    {
        abort_unless(auth()->user()->can('manage-settings'), 403);

        $this->validate(
            ['licence' => ['required', 'string', 'min:20']],
            [],
            ['licence' => __('نص الترخيص')],
        );

        $service = app(LicenseService::class);
        $status = $service->install($this->licence);

        if (in_array($status['state'], LicenseService::ALLOWED, true)) {
            $this->licence = '';
            $this->dispatch('toast', message: __('تم تفعيل الترخيص بنجاح'));

            return;
        }

        $this->addError('licence', $status['message'] ?: __('ملف الترخيص غير صالح أو تم تعديله.'));
    }

    public function render()
    {
        $service = app(LicenseService::class);

        return view('livewire.settings.license', [
            'status' => $service->status(),
            'machineCode' => $service->machineCode(),
            'version' => config('tasyiir.version'),
        ]);
    }
}
