<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Admin;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
