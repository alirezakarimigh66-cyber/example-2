<?php

namespace App\Http\Requests\Auth;

use App\Rules\HashablePassword;
use App\Support\IranianMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => [...IranianMobile::rules(), 'unique:users,phone'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers(), 'max:72', new HashablePassword, 'confirmed'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'نام خود را وارد کنید.',
            'phone.required' => 'شماره موبایل را وارد کنید.',
            'phone.regex' => 'شماره موبایل معتبر ایران وارد کنید؛ مانند 09123456789.',
            'phone.unique' => 'ثبت‌نام با این شماره امکان‌پذیر نیست. وارد شوید یا با پشتیبانی تماس بگیرید.',
            'password.required' => 'رمز عبور را وارد کنید.',
            'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
        ];
    }
}
