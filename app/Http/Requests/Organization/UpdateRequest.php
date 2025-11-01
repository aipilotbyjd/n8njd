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
        $orgId = $this->route('organization')->id;
        
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => "sometimes|string|max:255|alpha_dash|unique:organizations,slug,{$orgId}",
            'description' => 'nullable|string|max:1000',
            'plan' => 'sometimes|in:free,pro,enterprise',
            'is_active' => 'sometimes|boolean',
            'settings' => 'nullable|array',
        ];
    }
}
