<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:organizations,slug|alpha_dash',
            'description' => 'nullable|string|max:1000',
            'plan' => 'nullable|in:free,pro,enterprise',
        ];
    }
}
