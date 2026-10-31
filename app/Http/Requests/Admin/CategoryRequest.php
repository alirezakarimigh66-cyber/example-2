<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $category = $this->route('category');

        return ['name' => ['required', 'string', 'max:255', Rule::unique(Category::class, 'name')->ignore($category instanceof Category ? $category : null)]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.required' => 'نام دسته‌بندی را وارد کنید.', 'name.string' => 'نام دسته‌بندی باید متن باشد.', 'name.max' => 'نام دسته‌بندی نباید بیشتر از ۲۵۵ نویسه باشد.', 'name.unique' => 'این دسته‌بندی قبلاً ثبت شده است.'];
    }
}
