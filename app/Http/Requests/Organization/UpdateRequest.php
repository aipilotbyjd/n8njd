<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:organizations,slug,' . $this->route('organization')->id,
            'description' => 'nullable|string',
            'plan' => 'sometimes|in:free,pro,enterprise',
            'is_active' => 'sometimes|boolean',
            'settings' => 'nullable|array',
        ];
    }
}
