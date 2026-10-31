<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\ProductStatus;
use App\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => 'تکمیل :attribute الزامی است.',
            'string' => ':attribute باید متن باشد.',
            'name.max' => 'نام محصول نباید بیشتر از ۲۵۵ نویسه باشد.',
            'category_id.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
            'category_id.integer' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
            'description.max' => 'توضیحات نباید بیشتر از ۲۰۰۰۰ نویسه باشد.',
            'price.numeric' => 'قیمت باید عدد باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'price.max' => 'قیمت از محدوده مجاز بیشتر است.',
            'price.decimal' => 'قیمت حداکثر دو رقم اعشار دارد.',
            'stock_quantity.integer' => 'موجودی باید عدد صحیح باشد.',
            'stock_quantity.min' => 'موجودی نمی‌تواند منفی باشد.',
            'stock_quantity.max' => 'موجودی از محدوده مجاز بیشتر است.',
            'status.enum' => 'وضعیت انتخاب‌شده معتبر نیست.',
            'image.image' => 'فایل انتخاب‌شده باید تصویر معتبر باشد.',
            'image.mimes' => 'تصویر باید JPG، PNG یا WebP باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۴ مگابایت باشد.',
            'image.dimensions' => 'ابعاد تصویر نباید بیشتر از ۶۰۰۰ در ۶۰۰۰ پیکسل باشد.',
            'remove_image.boolean' => 'گزینه حذف تصویر معتبر نیست.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['name' => 'نام محصول', 'category_id' => 'دسته‌بندی', 'description' => 'توضیحات', 'price' => 'قیمت', 'stock_quantity' => 'موجودی', 'status' => 'وضعیت'];
    }
}
