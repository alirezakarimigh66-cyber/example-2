<?php

namespace App\Http\Requests\Auth;

use App\Support\IranianMobile;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    protected $redirectRoute = 'home';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => IranianMobile::normalize($this->input('phone'))]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'phone' => IranianMobile::rules(),
            'password' => ['required', 'string', 'max:1024'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل را وارد کنید.',
            'phone.regex' => 'شماره موبایل معتبر ایران وارد کنید؛ مانند 09123456789.',
            'password.required' => 'رمز عبور را وارد کنید.',
        ];
    }

    public function authenticate(): void
    {
        $key = 'login:'.hash('sha256', $this->string('phone').'|'.$this->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            event(new Lockout($this));
            throw ValidationException::withMessages([
                'phone' => 'تلاش‌های بیش از حد. لطفاً '.RateLimiter::availableIn($key).' ثانیه دیگر تلاش کنید.',
            ]);
        }

        RateLimiter::hit($key, 60);

        if (! Auth::attempt($this->safe()->only(['phone', 'password']), $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'phone' => 'شماره موبایل یا رمز عبور نادرست است.',
            ]);
        }

        RateLimiter::clear($key);
    }
}
