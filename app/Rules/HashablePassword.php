<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HashablePassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (strlen($value) > 72 || str_contains($value, "\0")) {
            $fail('رمز عبور باید حداکثر ۷۲ بایت باشد و نویسه تهی نداشته باشد.');
        }
    }
}
