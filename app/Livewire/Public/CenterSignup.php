<?php

namespace App\Livewire\Public;

use App\Models\CenterSignupRequest;
use App\Services\CenterProvisioner;
use App\Services\DemoData;
use App\Support\Demo;
use App\Support\Mode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Public center signup (hosted edition).
 *
 * With tasyiir.signup_requires_approval off (the default) the center is
 * provisioned immediately through the same CenterProvisioner the local
 * wizard and the admin approval use, and the owner is signed straight in.
 * With it on, this only files a CenterSignupRequest for a platform admin to
 * review — the original flow, unchanged.
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

    /** Online demo: start with sample students, courses, payments… */
    public bool $sample_data = true;

    protected function rules(): array
    {
        return [
            'center_name' => ['required', 'string', 'min:2', 'max:120'],
            'owner_name' => ['required', 'string', 'min:2', 'max:255'],
            'owner_email' => [
                'required', 'email', 'max:255',
                // Not an existing login, and — while requests are reviewed — not
                // already waiting. A rejected applicant may resubmit.
                Rule::unique('users', 'email'),
                Rule::when(Mode::signupRequiresApproval(), [
                    Rule::unique('center_signup_requests', 'owner_email')->where(fn ($q) => $q->where('status', '!=', 'rejected')),
                ]),
            ],
            // The demo needs a number to follow up with the prospect.
            'owner_phone' => [Demo::enabled() ? 'required' : 'nullable', 'string', 'max:30'],
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

    public function submit()
    {
        $this->ensureIsNotRateLimited();

        $data = $this->validate();

        if (Mode::signupRequiresApproval()) {
            CenterSignupRequest::create([
                'center_name' => trim($data['center_name']),
                'owner_name' => trim($data['owner_name']),
                'owner_email' => mb_strtolower(trim($data['owner_email'])),
                'owner_phone' => $data['owner_phone'] ?: null,
                'password' => Hash::make($data['password']), // never stored in clear
                'status' => 'pending',
            ]);

            RateLimiter::clear($this->throttleKey());
            $this->reset(['password', 'password_confirmation']);
            $this->submitted = true;

            return null;
        }

        // Instant signup: the center is live as soon as the form is submitted.
        ['tenant' => $tenant, 'owner' => $owner] = app(CenterProvisioner::class)->provision(
            $data['center_name'],
            $data['owner_name'],
            $data['owner_email'],
            $data['password'],
            $data['owner_phone'] ?: null,
            app()->getLocale(),
        );

        // Before Auth::login: the seeders must not run under the owner's tenant scope.
        if (Demo::enabled() && $this->sample_data) {
            app(DemoData::class)->fill($tenant);
        }

        RateLimiter::clear($this->throttleKey());

        Auth::login($owner);
        Session::regenerate();

        return redirect()->route('dashboard')
            ->with('toast', Demo::enabled()
                ? __('مرحباً بك! نسختك التجريبية المجانية مفعّلة لمدة :days أيام.', ['days' => config('tasyiir.demo.days')])
                : __('تم إنشاء مركزك بنجاح. مرحباً بك في TASYIIR!'));
    }

    /** Public form: cap attempts per IP. */
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
        return 'center-signup|'.request()->ip();
    }

    public function render()
    {
        return view('livewire.public.center-signup')
            ->layout('layouts.guest', ['maxWidth' => 'max-w-lg'])
            ->title(Demo::enabled() ? __('جرّب TASYIIR مجاناً') : __('تسجيل مركز جديد'));
    }
}
