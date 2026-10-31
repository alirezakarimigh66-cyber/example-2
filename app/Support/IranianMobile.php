<?php

namespace App\Support;

class IranianMobile
{
    public static function normalize(mixed $phone): mixed
    {
        if (! is_string($phone)) {
            return $phone;
        }

        $phone = strtr($phone, array_combine(
            mb_str_split('۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩'),
            str_split('01234567890123456789'),
        ));
        $phone = preg_replace('/[\s()\-]+/u', '', $phone);

        return preg_replace('/^(?:0098|\+98|98|0)(9[0-9]{9})$/D', '+98$1', $phone);
    }

    /** @return list<string> */
    public static function rules(): array
    {
        return ['required', 'string', 'regex:/^\+989[0-9]{9}$/D'];
    }
}
