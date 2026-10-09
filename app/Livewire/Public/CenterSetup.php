<?php

namespace App\Livewire\Public;

use App\Http\Middleware\SetLocale;
use App\Services\CenterProvisioner;
use App\Support\Mode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * First-run wizard of the local edition: the center owner creates their own
 * center and login, with nobody to approve it. Reachable only while no center
 * exists — afterwards /setup is a 404 forever.
 */
class CenterSetup extends Component
{
    public string $center_name = '';

    public string $owner_name = '';

    public string $owner_phone = '';

    public string $owner_email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $locale = 'ar';

    public function mount(): void
    {
        abort_unless(Mode::needsSetup(), 404);

        $this->locale = app()->getLocale();
    }

    protected function rules(): array
    {
        return [
            'center_name' => ['required', 'string', 'min:2', 'max:120'],
            'owner_name' => ['required', 'string', 'min:2', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'locale' => ['required', Rule::in(SetLocale::SUPPORTED)],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'center_name' => __('اسم المركز'),
            'owner_name' => __('اسم المدير'),
            'owner_phone' => __('رقم الهاتف'),
            'owner_email' => __('البريد الإلكتروني'),
            'password' => __('كلمة المرور'),
            'password_confirmation' => __('تأكيد كلمة المرور'),
            'locale' => __('لغة الواجهة'),
        ];
    }

    public function submit()
    {
        abort_unless(Mode::needsSetup(), 404);
        $this->ensureIsNotRateLimited();

        $data = $this->validate();

        ['owner' => $owner] = app(CenterProvisioner::class)->provision(
            $data['center_name'],
            $data['owner_name'],
            $data['owner_email'],
            $data['password'],
            $data['owner_phone'] ?: null,
            $data['locale'],
        );

        RateLimiter::clear($this->throttleKey());

        Auth::login($owner);
        Session::regenerate();
        Session::put('locale', $data['locale']);

        return redirect()->route('dashboard')
            ->with('toast', __('تم إنشاء مركزك بنجاح. مرحباً بك في TASYIIR!'));
    }

    /** The wizard is public until it runs once; don't let it be hammered. */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 10)) {
            RateLimiter::hit($this->throttleKey(), 600);

            return;
        }

        throw ValidationException::withMessages([
            'center_name' => __('عدد كبير جداً من المحاولات. حاول بعد :seconds ثانية.', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return 'center-setup|'.request()->ip();
    }

    public function render()
    {
        return view('livewire.public.center-setup')
            ->layout('layouts.guest', ['maxWidth' => 'max-w-lg'])
            ->title(__('إعداد المركز'));
    }
}
